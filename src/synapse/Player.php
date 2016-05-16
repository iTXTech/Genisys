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

use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\network\SourceInterface;
use pocketmine\Player as PMPlayer;
use pocketmine\utils\UUID;
use synapse\event\player\PlayerConnectEvent;
use synapse\network\protocol\spp\PlayerLoginPacket;
use synapse\network\protocol\spp\TransferPacket;

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

	public function transfer(string $hash){
		$clients = Synapse::getInstance()->getClientData();
		if(isset($clients[$hash])){
			$pk = new TransferPacket();
			$pk->uuid = $this->uuid;
			$pk->clientHash = $hash;
			Synapse::getInstance()->sendDataPacket($pk);

			$ip = $clients[$hash]["ip"];
			$port = $clients[$hash]["port"];
			
			$this->close("", "Transferred to $ip:$port");
			Synapse::getInstance()->removePlayer($this);
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
		$this->interface->putPacket($this, $packet, $needACK);
	}

	public function directDataPacket(DataPacket $packet, $needACK = false){
		$this->interface->putPacket($this, $packet, $needACK, true);
	}
}