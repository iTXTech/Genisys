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

class Spider extends Monster{
	const NETWORK_ID = 35;
	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.9;

	public $dropExp = [5, 5];
	
	public function getName() : string{
		return "Spider";
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Spider::NETWORK_ID;
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
		$drops = array(ItemItem::get(ItemItem::STRING, 0, 1));
		if ($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player) {
			if (mt_rand(0, 199) < 5) {
				switch (mt_rand(0, 2)) {
					case 0:
						$drops[] = ItemItem::get(ItemItem::IRON_INGOT, 0, 1);
						break;
					case 1:
						$drops[] = ItemItem::get(ItemItem::CARROT, 0, 1);
						break;
					case 2:
						$drops[] = ItemItem::get(ItemItem::POTATO, 0, 1);
						break;
				}
			}
		}
		return $drops;
	}
}