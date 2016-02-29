<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Wolf extends Animal{
	const NETWORK_ID = 14;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;

	public $dropExp = [1, 3];
	
	public function getName() : string{
		return "Wolf";
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Wolf::NETWORK_ID;
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
**
	 * Sets the Wold profession
	 *
	 * @param $profession
	 */
	public function setProfession($profession){
		$this->namedtag->Profession = new IntTag("Profession", $profession);
	}
	public function getProfession(){
		return $this->namedtag["Profession"];
	}
	public function isBaby(){
		return $this->getDataFlag(self::DATA_AGEABLE_FLAGS, self::DATA_FLAG_BABY);
	}
}
