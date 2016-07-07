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

	const MAGIC_BYTES = "\x35\xac\x66\xbf";

	private $receiveBuffer = "";
	private $sendBuffer = "";
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
		if($this->server->needReconnect and $this->connected){
			$this->connected = false;
			$this->server->needReconnect = false;
		}
		if($this->connected){
			$err = socket_last_error($this->socket->getSocket());
			if($err == 10057 or $err == 10054){
				$this->server->getLogger()->error("Synapse connection has disconnected unexpectedly");
				$this->connected = false;
				$this->server->setConnected(false);
			}else{
				$this->receiveBuffer .= @socket_read($this->socket->getSocket(), 2048, PHP_BINARY_READ);
				if($this->sendBuffer != ""){
					@socket_write($this->socket->getSocket(), $this->sendBuffer);
					$this->sendBuffer = "";
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
		$end = explode(self::MAGIC_BYTES, $this->receiveBuffer, 2);//用MAGIC_BYTES分割缓存，仅分割成两个
		if(count($end) <= 2){//如果存在两个及以下（废话）
			if(count($end) == 1){//如果只有一个
				if(strstr($end[0], self::MAGIC_BYTES)){//判断是否为MAGIC_BYTES结尾，是的话就是一个完整的包
					$this->receiveBuffer = "";//清空缓存
				}else{
					return null;
				}
			}else{
				$this->receiveBuffer = $end[1];//否则剩余缓存为数组的第一个成员
			}
			$buffer = $end[0];
			if(strlen($buffer) < 4){//如果长度小于4
				return null;
			}
			$len = Binary::readLInt(substr($buffer, 0, 4));//开头四个字节是包长度
			$buffer = substr($buffer, 4);//截取剩余的缓存，长度后面的都是包的内容了
			if($len != strlen($buffer)){//校验长度
				throw new \Exception("Wrong packet $buffer");
			}
			return $buffer;
		}
		return null;
	}

	public function writePacket($data){
		$this->sendBuffer .= Binary::writeLInt(strlen($data)) . $data . self::MAGIC_BYTES;
	}

}