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

use pocketmine\event\entity\CreeperPowerEvent;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Creeper extends Monster{
	const NETWORK_ID = 33;

	const DATA_SWELL = 19;
	const DATA_SWELL_OLD = 20;
	const DATA_SWELL_DIRECTION = 21;

	public $dropExp = [5, 5];
	
	public function getName() : string{
		return "Creeper";
	}

	public function initEntity(){
		parent::initEntity();

		if(!isset($this->namedtag->powered)){
			$this->setPowered(false);
		}
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_POWERED, $this->isPowered());
	}

	public function setPowered(bool $powered, Lightning $lightning = null){
		if($lightning != null){
			$powered = true;
			$cause = CreeperPowerEvent::CAUSE_LIGHTNING;
		}else $cause = $powered ? CreeperPowerEvent::CAUSE_SET_ON : CreeperPowerEvent::CAUSE_SET_OFF;

		$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new CreeperPowerEvent($this, $lightning, $cause));

		if(!$ev->isCancelled()){
			$this->namedtag->powered = new ByteTag("powered", $powered ? 1 : 0);
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_POWERED, $powered);
		}
	}

	public function isPowered() : bool{
		return (bool) $this->namedtag["powered"];
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Creeper::NETWORK_ID;
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