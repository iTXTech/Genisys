<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace synapse;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\ChangeDimensionPacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\protocol\PlayStatusPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\SetDifficultyPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetPlayerGameTypePacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\Player as PMPlayer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use synapse\event\player\PlayerConnectEvent;
use synapse\network\protocol\spp\PlayerLoginPacket;
use synapse\network\protocol\spp\TransferPacket;
use synapse\network\protocol\spp\FastPlayerListPacket;

class Player extends PMPlayer{
	private $isFirstTimeLogin = false;
	private $lastPacketTime;

	public function handleLoginPacket(PlayerLoginPacket $packet){
		$this->isFirstTimeLogin = $packet->isFirstTime;
		$this->server->getPluginManager()->callEvent($ev = new PlayerConnectEvent($this, $this->isFirstTimeLogin));
		$pk = Synapse::getInstance()->getPacket($packet->cachedLoginPacket);
		$pk->decode();
		$this->handleDataPacket($pk);
	}

	protected function processLogin(){
		if($this->isFirstTimeLogin){
			parent::processLogin();
		}else{
			if(!$this->server->isWhitelisted(strtolower($this->getName()))){
				$this->close($this->getLeaveMessage(), "Server is white-listed");

				return;
			}elseif($this->server->getNameBans()->isBanned(strtolower($this->getName())) or $this->server->getIPBans()->isBanned($this->getAddress()) or $this->server->getCIDBans()->isBanned($this->randomClientId)){
				$this->close($this->getLeaveMessage(), TextFormat::RED . "You are banned");

				return;
			}

			if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
				$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			}
			if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
				$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
			}

			foreach($this->server->getOnlinePlayers() as $p){
				if($p !== $this and strtolower($p->getName()) === strtolower($this->getName())){
					if($p->kick("logged in from another location") === false){
						$this->close($this->getLeaveMessage(), "Logged in from another location");
						return;
					}
				}elseif($p->loggedIn and $this->getUniqueId()->equals($p->getUniqueId())){
					if($p->kick("logged in from another location") === false){
						$this->close($this->getLeaveMessage(), "Logged in from another location");
						return;
					}
				}
			}

			$nbt = $this->server->getOfflinePlayerData($this->username);
			$this->playedBefore = ($nbt["lastPlayed"] - $nbt["firstPlayed"]) > 1;
			if(!isset($nbt->NameTag)){
				$nbt->NameTag = new StringTag("NameTag", $this->username);
			}else{
				$nbt["NameTag"] = $this->username;
			}
			if(!isset($nbt->Hunger) or !isset($nbt->Health) or !isset($nbt->MaxHealth)){
				$nbt->Hunger = new ShortTag("Hunger", 20);
				$nbt->Health = new ShortTag("Health", 20);
				$nbt->MaxHealth = new ShortTag("MaxHealth", 20);
			}
			$this->food = $nbt["Hunger"];
			$this->setMaxHealth($nbt["MaxHealth"]);
			Entity::setHealth(($nbt["Health"] <= 0) ? 20 : $nbt["Health"]);

			$this->gamemode = $nbt["playerGameType"] & 0x03;
			if($this->server->getForceGamemode()){
				$this->gamemode = $this->server->getGamemode();
				$nbt->playerGameType = new IntTag("playerGameType", $this->gamemode);
			}

			$this->allowFlight = $this->isCreative();


			if(($level = $this->server->getLevelByName($nbt["Level"])) === null){
				$this->setLevel($this->server->getDefaultLevel());
				$nbt["Level"] = $this->level->getName();
				$nbt["Pos"][0] = $this->level->getSpawnLocation()->x;
				$nbt["Pos"][1] = $this->level->getSpawnLocation()->y;
				$nbt["Pos"][2] = $this->level->getSpawnLocation()->z;
			}else{
				$this->setLevel($level);
			}

			if(!($nbt instanceof CompoundTag)){
				$this->close($this->getLeaveMessage(), "Invalid data");

				return;
			}

			$this->achievements = [];

			/** @var ByteTag $achievement */
			foreach($nbt->Achievements as $achievement){
				$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
			}

			$nbt->lastPlayed = new LongTag("lastPlayed", floor(microtime(true) * 1000));
			if($this->server->getAutoSave()){
				$this->server->saveOfflinePlayerData($this->username, $nbt, true);
			}

			Entity::__construct($this->level->getChunk($nbt["Pos"][0] >> 4, $nbt["Pos"][2] >> 4, true), $nbt);
			$this->loggedIn = true;
			$this->server->addOnlinePlayer($this);

			$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
			if($ev->isCancelled()){
				$this->close($this->getLeaveMessage(), $ev->getKickMessage());

				return;
			}

			if($this->isCreative()){
				$this->inventory->setHeldItemSlot(0);
			}else{
				$this->inventory->setHeldItemSlot($this->inventory->getHotbarSlotIndex(0));
			}

			if($this->spawnPosition === null and isset($this->namedtag->SpawnLevel) and ($level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level){
				$this->spawnPosition = new Position($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
			}
			$spawnPosition = $this->getSpawn();

			$pk = new SetTimePacket();
			$pk->time = $this->level->getTime();
			$pk->started = $this->level->stopTime == false;
			$this->dataPacket($pk);

			$pk = new SetSpawnPositionPacket();
			$pk->x = (int) $spawnPosition->x;
			$pk->y = (int) $spawnPosition->y;
			$pk->z = (int) $spawnPosition->z;
			$this->dataPacket($pk);

			$this->sendAttributes();

			$pk = new SetDifficultyPacket();
			$pk->difficulty = $this->server->getDifficulty();
			$this->dataPacket($pk);

			$pk = new SetPlayerGameTypePacket();
			$pk->gamemode = $this->gamemode & 0x01;
			$this->dataPacket($pk);
			$this->sendSettings();

			$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
				TextFormat::AQUA . $this->username . TextFormat::WHITE,
				$this->ip,
				$this->port,
				TextFormat::GREEN . $this->randomClientId . TextFormat::WHITE,
				$this->id,
				$this->level->getName(),
				round($this->x, 4),
				round($this->y, 4),
				round($this->z, 4)
			]));

			if($this->gamemode === Player::SPECTATOR){
				$pk = new ContainerSetContentPacket();
				$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
				$this->dataPacket($pk);
			}else{
				$pk = new ContainerSetContentPacket();
				$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
				$pk->slots = array_merge(Item::getCreativeItems(), $this->personalCreativeItems);
				$this->dataPacket($pk);
			}

			$pk = new SetEntityDataPacket();
			$pk->eid = 0;
			$pk->metadata = [self::DATA_LEAD_HOLDER => [self::DATA_TYPE_LONG, -1]];
			$this->dataPacket($pk);

			$this->forceMovement = $this->teleportPosition = $this->getPosition();
		}
	}

	public function transfer(string $hash){
		$clients = Synapse::getInstance()->getClientData();
		if(isset($clients[$hash])){
			foreach($this->getLevel()->getEntities() as $entity){
				if(isset($entity->hasSpawned[$this->getLoaderId()])){
					$entity->despawnFrom($this);
				}
			}
			$pk = new TransferPacket();
			$pk->uuid = $this->uuid;
			$pk->clientHash = $hash;
			Synapse::getInstance()->sendDataPacket($pk);

			/*$ip = $clients[$hash]["ip"];
			$port = $clients[$hash]["port"];

			$this->close("", "Transferred to $ip:$port");
			Synapse::getInstance()->removePlayer($this);*/
		}
	}

	public function handleDataPacket(DataPacket $packet){
		$this->lastPacketTime = microtime(true);
		return parent::handleDataPacket($packet);
	}

	public function onUpdate($currentTick){
		if((microtime(true) - $this->lastPacketTime) >= 5 * 60){//5 minutes time out
			$this->close("", "timeout");
			return false;
		}
		return parent::onUpdate($currentTick);
	}

	public function setUniqueId(UUID $uuid){
		$this->uuid = $uuid;
	}

	public function dataPacket(DataPacket $packet, $needACK = false){
		if($packet instanceof PlayerListPacket){
			$pk = new FastPlayerListPacket();
			$pk->sendTo = $this->uuid;
			$pk->type = $packet->type;
			foreach($packet->entries as $entry){
				if($packet->type !== PlayerListPacket::TYPE_REMOVE){
					array_pop($entry);
					array_pop($entry);
				}
				$pk->entries[] = $entry;
			}
			Synapse::getInstance()->sendDataPacket($pk);
			return;
		}
		$this->interface->putPacket($this, $packet, $needACK);
	}

	public function directDataPacket(DataPacket $packet, $needACK = false){
		if($packet instanceof PlayerListPacket){
			$pk = new FastPlayerListPacket();
			$pk->sendTo = $this->uuid;
			$pk->type = $packet->type;
			foreach($packet->entries as $entry){
				if($packet->type !== PlayerListPacket::TYPE_REMOVE){
					array_pop($entry);
					array_pop($entry);
				}
				$pk->entries[] = $entry;
			}
			Synapse::getInstance()->sendDataPacket($pk);
			return;
		}
		$this->interface->putPacket($this, $packet, $needACK, true);
	}
}
