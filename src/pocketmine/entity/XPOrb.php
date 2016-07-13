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
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\event\player\PlayerPickupExpOrbEvent;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class XPOrb extends Entity{
	const NETWORK_ID = 69;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.04;
	protected $drag = 0;
	
	protected $experience = 0;

	public function initEntity(){
		parent::initEntity();
		if(isset($this->namedtag->Experience)){
			$this->experience = $this->namedtag["Experience"];
		}else $this->close();
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}
		
		$tickDiff = $currentTick - $this->lastUpdate;
		
		$this->lastUpdate = $currentTick;
		
		$this->timings->startTiming();
		
		$hasUpdate = $this->entityBaseTick($tickDiff);

		$this->age++;

		if($this->age > 1200){
			$this->kill();
			$this->close();
			$hasUpdate = true;
		}
		
		$minDistance = PHP_INT_MAX;
		$expectedPos = null;
		foreach($this->getLevel()->getEntities() as $e){
			if($e instanceof Player and !$e->isSpectator()){
				if($e->distance($this) <= $minDistance) {
					$expectedPos = $e;
					$minDistance = $e->distance($this);
				}
			} 
		}

		if($minDistance < PHP_INT_MAX){
			$moveSpeed = 0.7;
			$motX = ($expectedPos->getX() - $this->x) / 8;
			$motY = ($expectedPos->getY() + $expectedPos->getEyeHeight() - $this->y) / 8;
			$motZ = ($expectedPos->getZ() - $this->z) / 8;
			$motSqrt = sqrt($motX * $motX + $motY * $motY + $motZ * $motZ);
			$motC = 1 - $motSqrt;
		
			if($motC > 0){
				$motC *= $motC;
				$this->motionX = $motX / $motSqrt * $motC * $moveSpeed;
				$this->motionY = $motY / $motSqrt * $motC * $moveSpeed;
				$this->motionZ = $motZ / $motSqrt * $motC * $moveSpeed;
			}

			$this->motionY -= $this->gravity;

			if($this->checkObstruction($this->x, $this->y, $this->z)){
				$hasUpdate = true;
			}

			if($this->isInsideOfSolid()){
				$this->setPosition($expectedPos);
			}

			if($minDistance <= 1.3){
				if($this->getLevel()->getServer()->expEnabled){
					if($this->getExperience() > 0){
						$this->kill();
						$this->close();

						$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new PlayerPickupExpOrbEvent($expectedPos, $this->getExperience()));
						if(!$ev->isCancelled()) $expectedPos->addExperience($this->getExperience());
					}
				}
			}
		}

		$this->move($this->motionX, $this->motionY, $this->motionZ);
		
		$this->updateMovement();
		
		$this->timings->stopTiming();

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}

	public function canCollideWith(Entity $entity){
		return false;
	}
	
	public function setExperience($exp){
		$this->experience = $exp;
	}
	
	public function getExperience(){
		return $this->experience;
	}

	public function spawnTo(Player $player){
		$this->setDataProperty(self::DATA_NO_AI, self::DATA_TYPE_BYTE, 1);
		$pk = new AddEntityPacket();
		$pk->type = XPOrb::NETWORK_ID;
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
