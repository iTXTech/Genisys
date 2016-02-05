<?php
namespace pocketmine\entity;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DustParticle;
class Boat extends Vehicle{
	const NETWORK_ID = 90;
	public $width = 1.5;
	public $length = 1.5;
	public $height = 0.6;
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Boat::NETWORK_ID;
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
	public function initEntity(){
		$this->setMaxHealth(6);
		parent::initEntity();
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
	public function kill(){
        parent::kill();
        foreach($this->getDrops() as $item){
            $this->getLevel()->dropItem($this, $item);
        	}
		//$this->getLevel()->addParticle(new DustParticle(new Vector3($this->x,$this->y,$this->z),0,0,255));
    	}
	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}
		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}
		$this->lastUpdate = $currentTick;
		$this->timings->startTiming();
		$hasUpdate = $this->entityBaseTick($tickDiff);
		if(!$this->level->getBlock(new Vector3($this->x,$this->y-0.20,$this->z))->getBoundingBox()==null or $this->isInsideOfWater()){
			$this->motionY = 0.1;
		}else{
			$this->motionY = -0.08;
		}
		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->updateMovement();
		if($this->linkedEntity == null or $this->linkedType = 0){
			if($this->age > 1500){
				$this->close();
				$hasUpdate = true;
				$this->scheduleUpdate();
				$this->age = 0;
			}
		}else $this->age = 0;
		$this->timings->stopTiming();
		return $hasUpdate or !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
	}
	public function getDrops(){
		$drops[] = ItemItem::get(ItemItem::BOAT, 0, 1);
		return $drops;
	}
	public function getSaveId(){
		$class = new \ReflectionClass(static::class);
		return $class->getShortName();
	}
}
