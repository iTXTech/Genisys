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

class Synapse{
	/** @var  Server */
	private $server;
	/** @var  MainLogger */
	private $logger;
	private $port;

	public function __construct(Server $server, int $port = 10305){
		$this->server = $server;
		$this->port = $port;
		$this->logger = $server->getLogger();
		$this->start();
	}

	public function getLogger(){
		return $this->logger;
	}

	public function start(){
		//connect to synapse server
	}
}