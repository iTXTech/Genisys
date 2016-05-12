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
 
namespace synapse;

use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\network\SourceInterface;
use pocketmine\Player as PMPlayer;
use synapse\network\protocol\spp\PlayerLoginPacket;

class Player extends PMPlayer{
	private $isFirstTimeLogin = false;

	public function handleLoginPacket(PlayerLoginPacket $packet){
		$this->isFirstTimeLogin = $packet->isFirstTime;
		$pk = new LoginPacket();
		$pk->buffer = $packet->cachedLoginPacket;
		$pk->decode();
		$this->handleDataPacket($pk);
	}

	public function dataPacket(DataPacket $packet, $needACK = false){
		$this->interface->putPacket($this, $packet, $needACK);
	}

	public function directDataPacket(DataPacket $packet, $needACK = false){
		$this->interface->putPacket($this, $packet, $needACK, true);
	}
}