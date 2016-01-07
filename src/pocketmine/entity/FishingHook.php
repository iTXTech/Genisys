<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/7 16:41
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class FishingHook extends Entity{
	const NETWORK_ID = 77;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.04;
	protected $drag = 0.02;

	public $canCollide = false;

	public function getName(){
		return "Fishing Hook";
	}



	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = FishingHook::NETWORK_ID;
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