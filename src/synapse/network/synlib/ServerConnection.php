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

	const MAGIC_BYTES = "\x35\xac";

	private $receivedData = "";
	private $sendData = [];
	/** @var resource */
	private $socket;
	private $ip;
	private $port;
	/** @var SynapseClient */
	private $server;

	protected $shutdown = false;

	public function __construct(SynapseClient $server, SynapseSocket $socket){
		$this->server = $server;
		$this->socket = $socket;
		socket_getpeername($this->socket->getSocket(), $address, $port);
		$this->ip = $address;
		$this->port = $port;

		$this->run();
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function run(){
		$this->tickProcessor();
	}

	private function tickProcessor(){
		while(!$this->shutdown){
			$start = microtime(true);
			$this->tick();
			$time = microtime(true);
			if($time - $start < 0.01){
				@time_sleep_until($time + 0.01 - ($time - $start));
			}
		}
	}

	private function tick(){
		$this->update();
		while(($data = $this->readPacket()) !== null){
			$this->server->pushThreadToMainPacket($data);
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
		if(count($this->sendData) > 0){
			$data = implode(self::MAGIC_BYTES, $this->sendData);
			socket_write($this->socket->getSocket(), $data);
			$this->sendData = [];
		}
		$data = socket_read($this->socket->getSocket(), 2048, PHP_BINARY_READ);
		$this->receivedData .= $data;
	}

	public function getSocket(){
		return $this->socket;
	}

	public function readPacket(){
		$end = explode(self::MAGIC_BYTES, $this->receivedData, 2);
		if(count($end) == 2){
			$this->receivedData = $end[1];
			if($end[0] == ''){
				return null;
			}
			$buffer = $end[0];
			if(strlen($buffer) < 4){
				return null;
			}
			$len = Binary::readLInt(substr($buffer, 1, 4));
			$buffer = substr($buffer, 4, strlen($buffer) - 4);
			if($len != strlen($buffer)){
				throw new \Exception("Wrong packet $buffer");
			}
			return $buffer;
		}
		return null;
	}

	public function writePacket($data){
		$this->sendData[] = Binary::writeLInt(strlen($data)) . $data;
	}

}