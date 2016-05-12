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

namespace synapse\network\synlib;


class SynapseSocket{
	private $socket;
	/** @var \ThreadedLogger */
	private $logger;
	private $interface;
	private $port;

	public function __construct(\ThreadedLogger $logger, $port = 10305, $interface = "127.0.0.1"){
		$this->logger = $logger;
		$this->interface = $interface;
		$this->port = $port;
		$this->connect();
	}

	public function connect(){
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false or !@socket_connect($this->socket, $this->interface, $this->port)){
			$this->logger->critical("Synapse Client can't connect $this->interface:$this->port");
			$this->logger->error("Socket error: " . socket_strerror(socket_last_error()));
			return false;
		}
		$this->logger->info("Synapse has connected to $this->interface:$this->port");
		socket_set_nonblock($this->socket);
		return true;
	}

	public function getSocket(){
		return $this->socket;
	}

	public function close(){
		socket_close($this->socket);
	}
}