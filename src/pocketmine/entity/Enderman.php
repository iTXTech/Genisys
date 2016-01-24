<?php
namespace pocketmine\entity;

use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\entity\Monster;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;

class Enderman extends Monster{
	const NETWORK_ID = 38;
	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;
	
	public function getName() : string{
		return "Enderman";
	}
	
	public function kill(){
		parent::kill();
		if($this->getLevel()->getServer()->expEnabled) $this->getLevel()->addExperienceOrb($this->add(0, 1, 0), 5);
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Enderman::NETWORK_ID;
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