<?php

namespace pocketmine\entity;

use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\math\Vector3;

class Minecart extends Vehicle{
	const NETWORK_ID = 84;

	public $height = 0.9;
	public $width = 1.1;

	public $drag = 0.1;
	public $gravity = 0.5;

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

		$hasUpdate = false;;
		//parent::onUpdate($currentTick);

		if($this->isAlive()){
			$this->motionY -= $this->gravity;

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

			if($this->isFreeMoving){
				$this->motionX = 0;
				$this->motionZ = 0;
				$this->isFreeMoving = false;
			}

			/*$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
			$this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI); //视角计算
			$this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);*/
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
