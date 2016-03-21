<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\Player;
use pocketmine\network\protocol\AddEntityPacket;

class ZombieVillager extends Zombie{
	const NETWORK_ID = 44;

	public $width = 1.031;
	public $length = 0.891;
	public $height = 2.125;

	public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
	}

	public function getName() : string{
		return "Zombie Villager";
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = ZombieVillager::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}