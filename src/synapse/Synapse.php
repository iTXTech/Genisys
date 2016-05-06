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
use synapse\network\protocol\spp\Info;
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

	public function __construct(Server $server, array $config){
		self::$obj = $this;
		$this->server = $server;
		$this->serverIp = $config["server-ip"];
		$this->port = $config["server-port"];
		$this->isMainServer = $config["isMainServer"];
		$this->password = $config["password"];
		$this->logger = $server->getLogger();
		$this->interface = new SynapseInterface($this, $this->serverIp, $this->port);
		$this->connect();
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

	public function getServerIp() : string {
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

	}
}