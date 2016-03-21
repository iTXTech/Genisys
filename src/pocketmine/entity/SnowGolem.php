<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class SnowGolem extends Animal{
	const NETWORK_ID = 21;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;
	
	public function initEntity(){
		$this->setMaxHealth(4);
		parent::initEntity();
	}
	
	public function getName() {
		return "Snow Golem";
	}
	
	public function spawnTo(Player $player) {
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = self::NETWORK_ID;
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