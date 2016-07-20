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
 
namespace synapse\network\protocol\spp;

use pocketmine\utils\UUID;

class BroadcastPacket extends DataPacket{

	const NETWORK_ID = Info::BROADCAST_PACKET;
	
	/** @var UUID[] */
	public $entries = [];
	public $direct;
	public $payload;
	
	public function encode(){
		$this->reset();
		$this->putByte($this->direct ? 1 : 0);
		$this->putShort(count($this->entries));
		foreach($this->entries as $uuid){
			$this->putUUID($uuid);
		}
		$this->putString($this->payload);
	}
	
	public function decode(){
		$this->direct = ($this->getByte() == 1) ? true : false;
		$len = $this->getShort();
		for($i = 0; $i < $len; $i++){
			$this->entries[] = $this->getUUID();
		}
		$this->payload = $this->getString();
	}
	
}
