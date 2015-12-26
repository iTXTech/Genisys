<?php

namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\math\Vector3;

class Minecart extends Entity{
	const NETWORK_ID = 84;
	
	public $height = 0.9;
	
	public $drag = 0.1;
	public $gravity = 0.05;
	
	public $isMoving = false;
	public $moveSpeed = 0.4;
	
	public $isFreeMoving = false;
	
	public function initEntity(){
		$this->setMaxHealth(1);
		$this->setHealth($this->getMaxHealth());
		parent::initEntity();
	}
	
	public function onUpdate($currentTick){
		if($this->closed !== false){
			return false;
		}
		
		$this->lastUpdate = $currentTick;

		$this->timings->startTiming();

		$hasUpdate = true;
		//parent::onUpdate($currentTick);
		
		if($this->isAlive()){
			$expectedPos = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);
			$expBlock0 = $this->getLevel()->getBlock($expectedPos->add(0, -1, 0)->round());
			$expBlock1 = $this->getLevel()->getBlock($expectedPos->add(0, 0, 0)->round());
			
			if($expBlock0->getId() == 0){
				$this->motionY -= $this->gravity;//重力计算
				$this->motionX = 0;
				$this->motionZ = 0;
			}else $this->motionY = 0;
			
			if($expBlock1->getId() != 0){
				$this->motionY += 0.1;
			}

			$this->move($this->motionX, $this->motionY, $this->motionZ);
			
			if($this->isFreeMoving){
				$this->motionX = 0;
				$this->motionZ = 0;
				$this->isFreeMoving = false;
			}
			
			/*$friction = 1 - $this->drag;

			$this->motionX *= $friction;
			$this->motionY *= 1 - $this->drag;
			$this->motionZ *= $friction;*/

			$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
			$this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI); //视角计算
			//$this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);
			
			$this->updateMovement();
		}
		
		$this->timings->stopTiming();

		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
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

	public function attack($damage, EntityDamageEvent $source){
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
	}
}
