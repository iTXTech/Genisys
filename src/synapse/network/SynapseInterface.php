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

use synapse\network\protocol\spp\BroadcastPacket;
use synapse\network\protocol\spp\ConnectPacket;
use synapse\network\protocol\spp\DataPacket;
use synapse\network\protocol\spp\DisconnectPacket;
use synapse\network\protocol\spp\FastPlayerListPacket;
use synapse\network\protocol\spp\HeartbeatPacket;
use synapse\network\protocol\spp\Info;
use synapse\network\protocol\spp\InformationPacket;
use synapse\network\protocol\spp\PlayerLoginPacket;
use synapse\network\protocol\spp\PlayerLogoutPacket;
use synapse\network\protocol\spp\RedirectPacket;
use synapse\network\protocol\spp\TransferPacket;
use synapse\network\synlib\SynapseClient;
use synapse\Synapse;

class SynapseInterface{
	private $synapse;
	private $ip;
	private $port;
	/** @var SynapseClient */
	private $client;
	/** @var DataPacket[] */
	private $packetPool = [];
	private $connected = true;
	
	public function __construct(Synapse $server, string $ip, int $port){
		$this->synapse = $server;
		$this->ip = $ip;
		$this->port = $port;
		$this->registerPackets();
		$this->client = new SynapseClient($server->getLogger(), $server->getGenisysServer()->getLoader(), $port, $ip);
	}

	public function getSynapse(){
		return $this->synapse;
	}

	public function reconnect(){
		$this->client->reconnect();
	}

	public function shutdown(){
		$this->client->shutdown();
	}

	public function putPacket(DataPacket $pk){
		if(!$pk->isEncoded){
			$pk->encode();
		}
		$this->client->pushMainToThreadPacket($pk->buffer);
	}

	public function isConnected() : bool{
		return $this->connected;
	}

	public function process(){
		while(strlen($buffer = $this->client->readThreadToMainPacket()) > 0){
			$this->handlePacket($buffer);
		}
		$this->connected = $this->client->isConnected();
		if($this->client->isNeedAuth()){
			$this->synapse->connect();
			$this->client->setNeedAuth(false);
		}
	}

	/**
	 * @param $buffer
	 *
	 * @return DataPacket
	 */
	public function getPacket($buffer) {
		$pid = ord($buffer{0});
		/** @var DataPacket $class */
		$class = $this->packetPool[$pid];
		if ($class !== null) {
			$pk = clone $class;
			$pk->setBuffer($buffer, 1);
			return $pk;
		}
		return null;
	}

	public function handlePacket($buffer){
		if(($pk = $this->getPacket($buffer)) != null){
			$pk->decode();
			$this->synapse->handleDataPacket($pk);
		}
	}

	/**
	 * @param int        $id 0-255
	 * @param DataPacket $class
	 */
	public function registerPacket($id, $class) {
		$this->packetPool[$id] = new $class;
	}


	private function registerPackets() {
		$this->packetPool = new \SplFixedArray(256);

		$this->registerPacket(Info::HEARTBEAT_PACKET, HeartbeatPacket::class);
		$this->registerPacket(Info::CONNECT_PACKET, ConnectPacket::class);
		$this->registerPacket(Info::DISCONNECT_PACKET, DisconnectPacket::class);
		$this->registerPacket(Info::REDIRECT_PACKET, RedirectPacket::class);
		$this->registerPacket(Info::PLAYER_LOGIN_PACKET, PlayerLoginPacket::class);
		$this->registerPacket(Info::PLAYER_LOGOUT_PACKET, PlayerLogoutPacket::class);
		$this->registerPacket(Info::INFORMATION_PACKET, InformationPacket::class);
		$this->registerPacket(Info::TRANSFER_PACKET, TransferPacket::class);
		$this->registerPacket(Info::BROADCAST_PACKET, BroadcastPacket::class);
		$this->registerPacket(Info::FAST_PLAYER_LIST_PACKET, FastPlayerListPacket::class);
	}
}
