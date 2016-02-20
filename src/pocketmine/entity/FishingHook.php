<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/7 16:41


 *
 * OpenGenisys Project
 */
namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class FishingHook extends Projectile{
	const NETWORK_ID = 77;

	const DATA_SOURCE_UUID = 23;
	const DATA_TARGET_UUID = 24;

	public $width = 0.2;
	public $length = 0.2;
	public $height = 0.2;
	protected $gravity = 0.04;
	protected $drag = 0.04;

	//public $canCollide = false;
	/** @var Player */
	public $owner = null;

	public $results = [
		[ItemItem::RAW_FISH, 0, 1],
	];

	public function getName() : string{
		return "Fishing Hook";
	}

	public function __construct(FullChunk $chunk, CompoundTag $nbt, Player $owner = null){
		if($owner == null){
			$this->close();
			return;
		}

		parent::__construct($chunk, $nbt);

		$this->owner = $owner;

		$this->setDataProperty(self::DATA_NO_AI, self::DATA_TYPE_BYTE, 1);
		$this->setDataProperty(self::DATA_SOURCE_UUID, self::DATA_TYPE_LONG, $this->owner->getId());
		$this->setDataProperty(self::DATA_TARGET_UUID, self::DATA_TYPE_LONG, $this->getId());
	}

	public function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);
	}

	public function close(){
		parent::close();

		if($this->owner instanceof Player){
			$this->owner->fishingHook = null;
		}
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		//$hasUpdate = parent::onUpdate($currentTick);
		$hasUpdate = false;

		$this->age++;

		if($this->age > 1200 or $this->owner == null){
			$this->close();
			$hasUpdate = true;
			if($this->owner instanceof  Player){
				if($this->isInsideOfWater()){
					//TODO: send results
				}
			}
		}

		if($this->isOnGround() or $this->isCollided){
			$this->motionX = 0;
			$this->motionY = 0;
			$this->motionZ = 0;
		}

		if($this->isInsideOfWater()) $this->motionY += 0.02;
		elseif(!$this->isOnGround() and !$this->isCollided) $this->motionY -= $this->gravity;

		$this->move($this->motionX, $this->motionY, $this->motionZ);

		if(!$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001){
			$f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
			$this->yaw = (atan2($this->motionX, $this->motionZ) * 180 / M_PI);
			$this->pitch = (atan2($this->motionY, $f) * 180 / M_PI);
			$hasUpdate = true;
		}

		$this->updateMovement();

		$friction = 1 - $this->drag;

		$this->motionX *= $friction;
		$this->motionY *= 1 - $this->drag;
		$this->motionZ *= $friction;

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function spawnTo(Player $player){
		if(!$this->owner instanceof Player){
			$this->close();
			return;
		}
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = FishingHook::NETWORK_ID;
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
}