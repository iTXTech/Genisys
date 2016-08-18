<?php

/* This file was taken from ClearSky. 
 * @link https://github.com/ClearSkyTeam/ClearSky 
 */

namespace pocketmine\entity;

use pocketmine\event\player\PlayerFishEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class FishingHook extends Projectile{
	const NETWORK_ID = self::FISHING_HOOK;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.1;
	protected $drag = 0.05;

	public $data = 0;
	public $attractTimer = 100;
	public $caughtTimer = 0;
	public $damageRod = false;

	public function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->Data)){
			$this->data = $this->namedtag["Data"];
		}

		// $this->setDataProperty(FallingSand::DATA_BLOCK_INFO, self::DATA_TYPE_INT, $this->getData());
	}

	public function __construct(FullChunk $chunk, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($chunk, $nbt, $shootingEntity);
	}

	public function setData($id){
		$this->data = $id;
	}

	public function getData(){
		return $this->data;
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->isCollidedVertically && $this->isInsideOfWater()){
			$this->motionX = 0;
			$this->motionY += 0.01;
			$this->motionZ = 0;
			$this->motionChanged = true;
			$hasUpdate = true;
		}elseif($this->isCollided && $this->keepMovement === true){
			$this->motionX = 0;
			$this->motionY = 0;
			$this->motionZ = 0;
			$this->motionChanged = true;
			$this->keepMovement = false;
			$hasUpdate = true;
		}
		if($this->attractTimer === 0 && mt_rand(0, 100) <= 30){ // chance, that a fish bites
			$this->caughtTimer = mt_rand(5, 10) * 20; // random delay to catch fish
			$this->attractTimer = mt_rand(30, 100) * 20; // reset timer
			$this->attractFish();
			if($this->shootingEntity instanceof Player) $this->shootingEntity->sendTip("A fish bites!");
		}elseif($this->attractTimer > 0){
			$this->attractTimer--;
		}
		if($this->caughtTimer > 0){
			$this->caughtTimer--;
			$this->fishBites();
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function fishBites(){
		if($this->shootingEntity instanceof Player){
			$pk = new EntityEventPacket();
			$pk->eid = $this->shootingEntity->getId();//$this or $this->shootingEntity
			$pk->event = EntityEventPacket::FISH_HOOK_HOOK;
			Server::broadcastPacket($this->shootingEntity->hasSpawned, $pk);
		}
	}

	public function attractFish(){
		if($this->shootingEntity instanceof Player){
			$pk = new EntityEventPacket();
			$pk->eid = $this->shootingEntity->getId();//$this or $this->shootingEntity
			$pk->event = EntityEventPacket::FISH_HOOK_BUBBLE;
			Server::broadcastPacket($this->shootingEntity->hasSpawned, $pk);
		}
	}

	public function reelLine(){
		$this->damageRod = false;

		if($this->shootingEntity instanceof Player && $this->caughtTimer > 0){
			$fishes = [ItemItem::RAW_FISH, ItemItem::RAW_SALMON, ItemItem::CLOWN_FISH, ItemItem::PUFFER_FISH];
			$fish = array_rand($fishes, 1);
			$item = ItemItem::get($fishes[$fish]);
			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new PlayerFishEvent($this->shootingEntity, $item, $this));
			if(!$ev->isCancelled()){
				$this->shootingEntity->getInventory()->addItem($item);
				$this->shootingEntity->addExperience(mt_rand(1, 6));
				$this->damageRod = true;
			}
		}

		if($this->shootingEntity instanceof Player){
			$this->shootingEntity->unlinkHookFromPlayer();
		}

		if(!$this->closed){
			$this->kill();
			$this->close();
		}

		return $this->damageRod;
	}
}
