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
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\level\format\Chunk;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Bat extends FlyingAnimal{

	const NETWORK_ID = 19;

	const DATA_IS_RESTING = 16;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 0.6;

	public $flySpeed = 0.8;
	public $switchDirectionTicks = 100;

	public function getName() : string {
		return "Bat";
	}

	public function initEntity(){
		$this->setMaxHealth(6);
		parent::initEntity();
	}

	public function __construct(Chunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->isResting)){
			$nbt->isResting = new ByteTag("isResting", 0);
		}
		parent::__construct($chunk, $nbt);

		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RESTING, $this->isResting());
	}

	public function isResting() : int{
		return (int) $this->namedtag["isResting"];
	}

	public function setResting(bool $resting){
		$this->namedtag->isResting = new ByteTag("isResting", $resting ? 1 : 0);
	}

	public function onUpdate($currentTick){
		if ($this->age > 20 * 60 * 10) {
			$this->kill();
		}
		return parent::onUpdate($currentTick);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Bat::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}