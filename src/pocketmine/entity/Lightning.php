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

use pocketmine\block\Liquid;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class Lightning extends Animal{
	const NETWORK_ID = 93;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;

	public function getName() : string{
		return "Lightning";
	}

	public function initEntity(){
		parent::initEntity();
		$this->setMaxHealth(2);
		$this->setHealth(2);
	}

	public function onUpdate($tick){
		parent::onUpdate($tick);
		if($this->age > 20){
			$this->kill();
			$this->close();
		}
		return true;
	}

	public function spawnTo(Player $player){
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

		$pk = new ExplodePacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->radius = 10;
		$pk->records = [];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function spawnToAll(){
		parent::spawnToAll();

		if($this->getLevel()->getServer()->lightningFire){
			$fire = ItemItem::get(ItemItem::FIRE)->getBlock();
			$oldBlock = $this->getLevel()->getBlock($this);
			if($oldBlock instanceof Liquid){

			}elseif($oldBlock->isSolid()){
				$v3 = new Vector3($this->x, $this->y + 1, $this->z);
			}else{
				$v3 = new Vector3($this->x, $this->y, $this->z);
			}
			if(isset($v3)) $this->getLevel()->setBlock($v3, $fire);

			foreach($this->level->getNearbyEntities($this->boundingBox->grow(4, 3, 4), $this) as $entity){
				if($entity instanceof Player){
					$damage = mt_rand(8, 20);
					$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_LIGHTNING, $damage);
					if($entity->attack($ev->getFinalDamage(), $ev) === true){
						$ev->useArmors();
					}
					$entity->setOnFire(mt_rand(3, 8));
				}

				if($entity instanceof Creeper){
					$entity->setPowered(true, $this);
				}
			}
		}
	}
}