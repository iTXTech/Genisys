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
 * @link https://mcper.cn
 *
 */

namespace synapse;

use pocketmine\Server;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;
use synapse\network\protocol\spp\ConnectPacket;
use synapse\network\protocol\spp\DataPacket;
use synapse\network\protocol\spp\HeartbeatPacket;
use synapse\network\protocol\spp\Info;
use synapse\network\protocol\spp\InformationPacket;
use synapse\network\protocol\spp\PlayerLoginPacket;
use synapse\network\SynapseInterface;

class Synapse{
	private static $obj = null;
	/** @var  Server */
	private $server;
	/** @var  MainLogger */
	private $logger;
	private $serverIp;
	private $port;
	private $isMainServer;
	private $password;
	private $interface;
	private $isVerified = false;
	private $lastUpdate;
	/** @var Player[] */
	private $players = [];

	public function __construct(Server $server, array $config){
		self::$obj = $this;
		$this->server = $server;
		$this->serverIp = $config["server-ip"];
		$this->port = $config["server-port"];
		$this->isMainServer = $config["isMainServer"];
		$this->password = $config["password"];
		$this->logger = $server->getLogger();
		$this->interface = new SynapseInterface($this, $this->serverIp, $this->port);
		$this->lastUpdate = time();
		$this->connect();
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

	public function connect(){
		$pk = new ConnectPacket();
		$pk->encodedPassword = base64_encode(Utils::aes_encode($this->password, $this->password));
		$pk->isMainServer = $this->isMainServer();
		$pk->maxPlayers = $this->server->getMaxPlayers();
		$pk->protocol = Info::CURRENT_PROTOCOL;
		$this->interface->putPacket($pk);
	}

	public function tick(){
		$this->interface->process();
		if((($time = time()) - $this->lastUpdate) > 5){//Heartbeat!
			$this->lastUpdate = $time;
			$pk = new HeartbeatPacket();
			$pk->tps = $this->server->getTicksPerSecondAverage();
			$pk->load = $this->server->getTickUsageAverage();
			$pk->upTime = microtime(true) - \pocketmine\START_TIME;
			$this->interface->putPacket($pk);
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

	public function getLogger(){
		return $this->logger;
	}

	public function handleDataPacket(DataPacket $pk){
		$this->logger->debug("Received packet " . $pk::NETWORK_ID . " from {$this->serverIp}:{$this->port}");
		switch($pk::NETWORK_ID){
			case Info::INFORMATION_PACKET:
				if($pk->message == InformationPacket::INFO_LOGIN_SUCCESS){
					$this->logger->info("Login success to {$this->serverIp}:{$this->port}");
					$this->isVerified = true;
				}elseif($pk->message == InformationPacket::INFO_LOGIN_FAILED){
					$this->logger->info("Login failed to {$this->serverIp}:{$this->port}");
				}
				break;
			case Info::PLAYER_LOGIN_PACKET:
				/** @var PlayerLoginPacket $pk */
				//$this->players[$pk->uuid->toBinary()] = new Player($this->server->getNetwork()->getInterfaces()[0]);
		}
	}
}