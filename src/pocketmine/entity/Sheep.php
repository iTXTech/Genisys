<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;

class Sheep extends Animal{
	const NETWORK_ID = 13;

	const DATA_COLOR_INFO = 16;

	public $width = 0.3;
	public $length = 0.6;
	public $height = 1.8;
	
	public function getName() : string{
		return "Sheep";
	}

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Color)){
			$nbt->Color = new ByteTag("Color", mt_rand(1, 15));
		}
		parent::__construct($chunk, $nbt);

		$this->setDataProperty(self::DATA_COLOR_INFO, self::DATA_TYPE_BYTE, $this->getColor());
	}

	public function getColor() : int{
		return (int) $this->namedtag["Color"];
	}

	public function kill(){
		parent::kill();
		if($this->getLevel()->getServer()->expEnabled) $this->getLevel()->addExperienceOrb($this->add(0, 1, 0), mt_rand(1, 3));
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Sheep::NETWORK_ID;
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
		$drops = array(ItemItem::get(ItemItem::WOOL, 0, 1));
		return $drops;
	}
}