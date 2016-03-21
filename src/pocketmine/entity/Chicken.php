<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;

class Chicken extends Animal{
	const NETWORK_ID = 10;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

	public $dropExp = [1, 3];
	
	public function getName() : string{
		return "Chicken";
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Chicken::NETWORK_ID;
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
	
	public function getDrops(){
		$drops = [];
		if ($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player) {
			
				switch (\mt_rand(0, 2)) {
					case 0:
						$drops[] = ItemItem::get(ItemItem::RAW_CHICKEN, 0, 1);
						break;
					case 1:
						$drops[] = ItemItem::get(ItemItem::FEATHER, 0, 1);
						break;
					case 2:
						$drops[] = ItemItem::get(ItemItem::FEATHER, 0, 2);
						break;
				}
		}
		return $drops;
	}
}