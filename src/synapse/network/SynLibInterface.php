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

use pocketmine\network\protocol\DataPacket;
use pocketmine\network\SourceInterface;
use pocketmine\Player;
use synapse\network\protocol\spp\RedirectPacket;
use synapse\Synapse;

class SynLibInterface implements SourceInterface{
	private $synapseInterface;
	private $synapse;

	public function __construct(Synapse $synapse, SynapseInterface $interface){
		$this->synapse = $synapse;
		$this->synapseInterface = $interface;
	}

	public function emergencyShutdown(){
	}

	public function setName($name){
	}

	public function process(){
	}

	public function close(Player $player, $reason = "unknown reason"){
	}

	public function putPacket(Player $player, DataPacket $packet, $needACK = false, $immediate = true){
		$packet->encode();
		$pk = new RedirectPacket();
		$pk->uuid = $player->getUniqueId();
		$pk->direct = $immediate;
		$pk->mcpeBuffer = $packet->buffer;
		$this->synapseInterface->putPacket($pk);
	}

	public function shutdown(){
	}
}