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

namespace synapse\network;

use pocketmine\Thread;
use pocketmine\utils\Binary;
use pocketmine\utils\MainLogger;
use synapse\Synapse;

class SynapseSocket extends Thread{
	private $interface;
	private $ip;
	private $port;
	private $socket;
	private $stop = false;

	public function __construct(string $ip, int $port){
		//$this->getInterface() = $interface;
		$this->ip = $ip;
		$this->port = $port;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if($this->socket === false or !@socket_connect($this->socket, $this->ip, $this->port)){
			$this->getLogger()->critical("Synapse Client can't connect to: " . socket_strerror(socket_last_error()));
			return;
		}
		socket_set_block($this->socket);

		socket_getsockname($this->socket, $addr, $port);
		$this->getLogger()->info("Synapse Client has connected to $addr:$port");
		$this->start();
	}

	public function getLogger(){
		return MainLogger::getLogger();
	}
	
	public function getInterface(){
		return Synapse::getInstance()->getInterface();
	}

	public function getSocket(){
		return $this->socket;
	}

	public function close(){
		socket_close($this->socket);
	}

	public function writePacket($buffer){ var_dump($buffer);
		return socket_write($this->socket, Binary::writeLInt(strlen($buffer)) . $buffer);
	}

	public function readPacket(&$buffer){
		socket_set_nonblock($this->socket);
		$d = @socket_read($this->socket, 4);
		if($this->stop === true){
			return false;
		}elseif($d === false){
			return null;
		}elseif($d === "" or strlen($d) < 4){
			return false;
		}
		socket_set_block($this->socket);
		$size = Binary::readLInt($d);
		if($size < 0 or $size > 65535){
			return false;
		}
		$buffer = rtrim(socket_read($this->socket, $size + 2)); //Strip two null bytes
		return true;
	}

	public function disconnect(){
		@socket_set_option($this->socket, SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
		@socket_shutdown($this->socket, 2);
		@socket_set_block($this->socket);
		@socket_read($this->socket, 1);
		@socket_close($this->socket);
	}

	public function run(){
		while(!$this->stop){
			$this->synchronized(function(){
				$this->wait(100);
			});
			$client = &$this->socket;
			if($client !== null and !$this->stop){
				$p = $this->readPacket($buffer);
				if($p === false){
					$this->disconnect();
					continue;
				}elseif($p === null){
					continue;
				}
				$this->getInterface()->handlePacket($buffer);
			}
		}
	}

	public function getThreadName(){
		return "SynapseServer";
	}
}