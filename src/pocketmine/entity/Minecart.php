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
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://mcper.cn
 *
 */

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\block\Rail;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\math\Math;

class Minecart extends Vehicle{
	const NETWORK_ID = 84;

	const TYPE_NORMAL = 1;
	const TYPE_CHEST = 2;
	const TYPE_HOPPER = 3;
	const TYPE_TNT = 4;

	public $height = 0.9;
	public $width = 1.1;

	public $drag = 0.1;
	public $gravity = 0.5;

	public $isMoving = false;
	public $moveSpeed = 0.4;

	public function initEntity(){
		$this->setMaxHealth(1);
		$this->setHealth($this->getMaxHealth());
		parent::initEntity();
	}

	public function getName() : string{
		return "Minecart";
	}

	public function getType() : int{
		return self::TYPE_NORMAL;
	}

	public function onUpdate($currentTick){
		if($this->closed !== false){
			return false;
		}

		$this->lastUpdate = $currentTick;

		$this->timings->startTiming();

		$hasUpdate = false;
		//parent::onUpdate($currentTick);

		if($this->isAlive()){
			$movingType = $this->getLevel()->getServer()->minecartMovingType;
			if($movingType == -1) return false;
			elseif($movingType == 0){
				$p = $this->getLinkedEntity();
				if($p instanceof Player){
					$this->motionX = -sin($p->getYaw() / 180 * M_PI);
					$this->motionZ = cos($p->getYaw() / 180 * M_PI);
				}
				$target = $this->getLevel()->getBlock($this->add($this->motionX, 0, $this->motionZ)->round());
				$target2 = $this->getLevel()->getBlock($this->add($this->motionX, 0, $this->motionZ)->floor());
				if($target->getId() != ItemItem::AIR or $target2->getId() != ItemItem::AIR) $this->motionY = $this->gravity * 3;
				else $this->motionY -= $this->gravity;

				if($this->checkObstruction($this->x, $this->y, $this->z)){
					$hasUpdate = true;
				}

				$this->move($this->motionX, $this->motionY, $this->motionZ);
				$this->updateMovement();

				$friction = 1 - $this->drag;

				if($this->onGround and (abs($this->motionX) > 0.00001 or abs($this->motionZ) > 0.00001)){
					$friction = $this->getLevel()->getBlock($this->temporalVector->setComponents((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z) - 1))->getFrictionFactor() * $friction;
				}

				$this->motionX *= $friction;
				$this->motionY *= 1 - $this->drag;
				$this->motionZ *= $friction;

				if($this->onGround){
					$this->motionY *= -0.5;
				}
			}elseif($movingType == 1){
				$p = $this->getLinkedEntity();
				if($p instanceof Player){
					$rail = $this->getNearestRail();
					if($rail !== null){
						$this->setPosition($rail);
						$motX = -sin($p->getYaw() / 180 * M_PI);
						$motZ = cos($p->getYaw() / 180 * M_PI);
						if($motX < 0) $motX = -0.5;
						elseif($motX > 0) $motX = 0.5;
						if($motZ < 0) $motZ = -0.5;
						elseif($motZ > 0) $motZ = 0.5;
						$block = $this->getLevel()->getBlock($this->add($motX, 0, $motZ)->round());
						if($block instanceof Rail){
							if($block->check($rail)){
								if($block->y > $rail->y) $motY = 1.0;
								else $motY = 0;
								$f = sqrt(($motX ** 2) + ($motZ ** 2));
								$this->yaw = (-atan2($motX, $motZ) * 180 / M_PI);
								$this->pitch = (-atan2($f, $motY) * 180 / M_PI);
								$this->move($motX, $motY, $motZ);
							}
						}
						$this->updateMovement();
					}
				}
				$hasUpdate = true;
			}
		}

		$this->timings->stopTiming();

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}

	/**
	 * @return Rail
	 */
	public function getNearestRail(){
		$minX = Math::floorFloat($this->boundingBox->minX);
		$minY = Math::floorFloat($this->boundingBox->minY);
		$minZ = Math::floorFloat($this->boundingBox->minZ);
		$maxX = Math::ceilFloat($this->boundingBox->maxX);
		$maxY = Math::ceilFloat($this->boundingBox->maxY);
		$maxZ = Math::ceilFloat($this->boundingBox->maxZ);

		$rails = [];

		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					$block = $this->level->getBlock($this->temporalVector->setComponents($x, $y, $z));
					if(in_array($block->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL])) $rails[] = $block;
				}
			}
		}

		$minDistance = PHP_INT_MAX;
		$nearestRail = null;
		foreach($rails as $rail){
			$dis = $this->distance($rail);
			if($dis < $minDistance){
				$nearestRail = $rail;
				$minDistance = $dis;
			}
		}
		return $nearestRail;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Minecart::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	/*public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);

		if(!$source->isCancelled()){
			$pk = new EntityEventPacket();
			$pk->eid = $this->id;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			foreach($this->getLevel()->getPlayers() as $player){
				$player->dataPacket($pk);
			}
		}
	}

	public function getSaveId(){
		$class = new \ReflectionClass(static::class);
		return $class->getShortName();
	}*/
}
