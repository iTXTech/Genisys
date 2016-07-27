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

use pocketmine\utils\Binary;

class ServerConnection{

	private $receiveBuffer = "";
	/** @var resource */
	private $socket;
	private $ip;
	private $port;
	/** @var SynapseClient */
	private $server;
	private $lastCheck;
	private $connected;

	public function __construct(SynapseClient $server, SynapseSocket $socket){
		$this->server = $server;
		$this->socket = $socket;
		@socket_getpeername($this->socket->getSocket(), $address, $port);
		$this->ip = $address;
		$this->port = $port;

		$this->lastCheck = microtime(true);
		$this->connected = true;

		$this->run();
	}

	public function run(){
		$this->tickProcessor();
	}

	private function tickProcessor(){
		while(!$this->server->isShutdown()){
			$start = microtime(true);
			$this->tick();
			$time = microtime(true);
			if($time - $start < 0.01){
				@time_sleep_until($time + 0.01 - ($time - $start));
			}
		}
		$this->tick();
		$this->socket->close();
	}

	private function tick(){
		$this->update();
		if(($data = $this->readPacket()) !== null){
			foreach($data as $pk){
				$this->server->pushThreadToMainPacket($pk);
			}
		}
		while(strlen($data = $this->server->readMainToThreadPacket()) > 0){
			$this->writePacket($data);
		}
	}

	public function getHash(){
		return $this->ip . ':' . $this->port;
	}

	public function getIp() : string{
		return $this->ip;
	}

	public function getPort() : int{
		return $this->port;
	}

	public function update(){
		if($this->server->needReconnect and $this->connected){
			$this->connected = false;
			$this->server->needReconnect = false;
		}
		if($this->connected){
			$err = socket_last_error($this->socket->getSocket());
			socket_clear_error($this->socket->getSocket());
			if($err == 10057 or $err == 10054){
				$this->server->getLogger()->error("Synapse connection has disconnected unexpectedly");
				$this->connected = false;
				$this->server->setConnected(false);
			}else{
				$data = @socket_read($this->socket->getSocket(), 65535, PHP_BINARY_READ);
				if($data != ""){
					$this->receiveBuffer .= $data;
				}
			}
		}else{
			if((($time = microtime(true)) - $this->lastCheck) >= 3){//re-connect
				$this->server->getLogger()->notice("Trying to re-connect to Synapse Server");
				if($this->socket->connect()){
					$this->connected = true;
					@socket_getpeername($this->socket->getSocket(), $address, $port);
					$this->ip = $address;
					$this->port = $port;
					$this->server->setConnected(true);
					$this->server->setNeedAuth(true);
				}
				$this->lastCheck = $time;
			}
		}
	}

	public function getSocket(){
		return $this->socket;
	}

	public function readPacket(){
		$packets = [];
		if($this->receiveBuffer !== "" && strlen($this->receiveBuffer) > 0){
			$len = strlen($this->receiveBuffer);
			$offset = 0;
			while($offset < $len){
				if($offset > $len - 4) break;
				$pkLen = Binary::readInt(substr($this->receiveBuffer, $offset, 4));
				$offset +=  4;

				if($pkLen <= ($len - $offset)) {
					$buf = substr($this->receiveBuffer, $offset, $pkLen);
					$offset += $pkLen;

					$packets[] = $buf;
				} else {
					$offset -= 4;
					break;
				}
			}
			if($offset < $len){
				$this->receiveBuffer = substr($this->receiveBuffer, $offset);
			}else{
				$this->receiveBuffer = "";
			}
		}

		return $packets;
	}

	public function writePacket($data){
		@socket_write($this->socket->getSocket(), Binary::writeInt(strlen($data)) . $data);
	}

}
