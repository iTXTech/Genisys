<?php

namespace pocketmine\entity;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Fireball extends Projectile{
  
	const NETWORK_ID = 85;
	
	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.1;
	protected $drag = 0;
	
	public function __construct(FullChunk $chunk, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($chunk, $nbt, $shootingEntity);
	}
	
	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}
		$this->timings->startTiming();
		$hasUpdate = parent::onUpdate($currentTick);
		
		if($this->age > 1200 or $this->isCollided){
			$this->kill();
			$this->setOnFire(PHP_INT_MAX);
			$hasUpdate = true;
		}
		
		$this->timings->stopTiming();
		
		return $hasUpdate;
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Fireball::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = [];
		$player->dataPacket($pk);
		
		parent::spawnTo($player);
	}
}
