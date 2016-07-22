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

use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;
use synapse\network\protocol\spp\BroadcastPacket;
use synapse\network\protocol\spp\ConnectPacket;
use synapse\network\protocol\spp\DataPacket;
use synapse\network\protocol\spp\DisconnectPacket;
use synapse\network\protocol\spp\HeartbeatPacket;
use synapse\network\protocol\spp\Info;
use synapse\network\protocol\spp\InformationPacket;
use synapse\network\protocol\spp\PlayerLoginPacket;
use synapse\network\protocol\spp\PlayerLogoutPacket;
use synapse\network\protocol\spp\RedirectPacket;
use synapse\network\SynapseInterface;
use synapse\network\SynLibInterface;

class Synapse{
	private static $obj = null;
	/** @var Server */
	private $server;
	/** @var MainLogger */
	private $logger;
	private $serverIp;
	private $port;
	private $isMainServer;
	private $password;
	private $interface;
	private $verified = false;
	private $lastUpdate;
	private $lastRecvInfo;
	/** @var Player[] */
	private $players = [];
	/** @var SynLibInterface */
	private $synLibInterface;
	private $clientData = [];
	/*
		$client->getHash() => [
			"ip" => $client->getIp(),
			"port" => $client->getPort(),
			"playerCount" => count($client->getPlayers()),
			"maxPlayers" => $client->getMaxPlayers(),
			"description" => $client->getDescription(),
		]
	 */
	private $description;
	private $connectionTime = PHP_INT_MAX;

	public function __construct(Server $server, array $config){
		self::$obj = $this;
		$this->server = $server;
		$this->serverIp = $config["server-ip"];
		$this->port = $config["server-port"];
		$this->isMainServer = $config["isMainServer"];
		$this->password = $config["password"];
		$this->description = $config["description"];
		$this->logger = $server->getLogger();
		$this->interface = new SynapseInterface($this, $this->serverIp, $this->port);
		$this->synLibInterface = new SynLibInterface($this, $this->interface);
		$this->lastUpdate = microtime(true);
		$this->lastRecvInfo = microtime(true);
		$this->connect();
	}

	public function getClientData(){
		return $this->clientData;
	}

	public function getGenisysServer(){
		return $this->server;
	}

	public function getInterface(){
		return $this->interface;
	}

	public static function getInstance(){
		return self::$obj;
	}

	public function shutdown(){
		if($this->verified){
			$pk = new DisconnectPacket();
			$pk->type = DisconnectPacket::TYPE_GENERIC;
			$pk->message = "Server closed";
			$this->sendDataPacket($pk);
			$this->getLogger()->debug("Synapse client has disconnected from Synapse server");
		}
	}

	public function getDescription() : string{
		return $this->description;
	}

	public function setDescription(string $description){
		$this->description = $description;
	}

	public function sendDataPacket(DataPacket $pk){
		$this->interface->putPacket($pk);
	}

	public function connect(){
		$this->verified = false;
		$pk = new ConnectPacket();
		$pk->password = $this->password;
		$pk->isMainServer = $this->isMainServer();
		$pk->description = $this->description;
		$pk->maxPlayers = $this->server->getMaxPlayers();
		$pk->protocol = Info::CURRENT_PROTOCOL;
		$this->sendDataPacket($pk);
		$this->connectionTime = microtime(true);
	}

	public function tick(){
		$this->interface->process();
		if((($time = microtime(true)) - $this->lastUpdate) >= 5){//Heartbeat!
			$this->lastUpdate = $time;
			$pk = new HeartbeatPacket();
			$pk->tps = $this->server->getTicksPerSecondAverage();
			$pk->load = $this->server->getTickUsageAverage();
			$pk->upTime = microtime(true) - \pocketmine\START_TIME;
			$this->sendDataPacket($pk);
		}
		if(((($time = microtime(true)) - $this->lastUpdate) >= 30) and $this->interface->isConnected()){//30 seconds timeout
			$this->interface->reconnect();
		}
		if(microtime(true) - $this->connectionTime >= 15 and !$this->verified){
			$this->interface->reconnect();
		}
	}

	public function getServerIp() : string{
		return $this->serverIp;
	}

	public function getPort() : int{
		return $this->port;
	}

	public function isMainServer() : bool{
		return $this->isMainServer;
	}

 	public function broadcastPacket(array $players, DataPacket $packet, $direct = false){
 		$packet->encode();
		$pk = new BroadcastPacket();
 		$pk->direct = $direct;
		$pk->payload = $packet->getBuffer();
 		foreach($players as $player){
			$pk->entries[] = $player->getUniqueId();
 		}
 		$this->sendDataPacket($pk);
 	}

	public function getLogger(){
		return $this->logger;
	}

	public function getHash() : string{
		return $this->serverIp . ":" . $this->port;
	}

	public function getPacket($buffer){
		$pid = ord($buffer{0});
		$start = 1;
		if($pid == 0xfe){
			$pid = ord($buffer{1});
			$start++;
		}
		if(($data = $this->getGenisysServer()->getNetwork()->getPacket($pid)) === null){
			return null;
		}
		$data->setBuffer($buffer, $start);

		return $data;
	}

	public function removePlayer(Player $player){
		if(isset($this->players[$uuid = $player->getUniqueId()->toBinary()])){
			unset($this->players[$uuid]);
		}
	}

	public function handleDataPacket(DataPacket $pk){
		$this->logger->debug("Received packet " . $pk::NETWORK_ID . " from {$this->serverIp}:{$this->port}");
		switch($pk::NETWORK_ID){
			case Info::DISCONNECT_PACKET:
				/** @var DisconnectPacket $pk */
				$this->verified = false;
				switch($pk->type){
					case DisconnectPacket::TYPE_GENERIC:
						$this->getLogger()->notice("Synapse Client has disconnected due to " . $pk->message);
						$this->interface->reconnect();
						break;
					case DisconnectPacket::TYPE_WRONG_PROTOCOL:
						$this->getLogger()->error($pk->message);
						break;
				}
				break;
			case Info::INFORMATION_PACKET:
				/** @var InformationPacket $pk */
				switch($pk->type){
					case InformationPacket::TYPE_LOGIN:
						if($pk->message == InformationPacket::INFO_LOGIN_SUCCESS){
							$this->logger->info("Login success to {$this->serverIp}:{$this->port}");
							$this->verified = true;
						}elseif($pk->message == InformationPacket::INFO_LOGIN_FAILED){
							$this->logger->info("Login failed to {$this->serverIp}:{$this->port}");
						}
					break;
					case InformationPacket::TYPE_CLIENT_DATA:
						$this->clientData = json_decode($pk->message, true)["clientList"];
						$this->lastRecvInfo = microtime();
						break;
				}
				break;
			case Info::PLAYER_LOGIN_PACKET:
				/** @var PlayerLoginPacket $pk */
				$player = new Player($this->synLibInterface, mt_rand(0, PHP_INT_MAX), $pk->address, $pk->port);
				$player->setUniqueId($pk->uuid);
				$this->server->addPlayer(spl_object_hash($player), $player);
				$this->players[$pk->uuid->toBinary()] = $player;
				$player->handleLoginPacket($pk);
				break;
			case Info::REDIRECT_PACKET:
				/** @var RedirectPacket $pk */
				if(isset($this->players[$uuid = $pk->uuid->toBinary()])){
					$pk = $this->getPacket($pk->mcpeBuffer);
					if($pk != null){//drop unknown packet
						$pk->decode();
						$this->players[$uuid]->handleDataPacket($pk);
					}
				}
				break;
			case Info::PLAYER_LOGOUT_PACKET:
				/** @var PlayerLogoutPacket $pk */
				if(isset($this->players[$uuid = $pk->uuid->toBinary()])){
					$this->players[$uuid]->close("", $pk->reason, false);
					$this->removePlayer($this->players[$uuid]);
				}
				break;
		}
	}
}
