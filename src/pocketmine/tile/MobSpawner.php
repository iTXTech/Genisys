<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/19 15:46
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */

namespace pocketmine\tile;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\level\format\FullChunk;
use pocketmine\Player;

class MobSpawner extends Spawnable implements Nameable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Data)){
			$nbt->Data = new IntTag("Data", 0);
		}
		parent::__construct($chunk, $nbt);
		$this->lastUpdate = $this->getLevel()->getServer()->getTick();
		if($this->getData() > 0) $this->scheduleUpdate();
	}

	public function getData(){
		return $this->namedtag["Data"];
	}

	public function setData($data){
		$this->namedtag->Data = new IntTag("Data", $data);
		$this->spawnToAll();
		if($this->chunk instanceof FullChunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
		$this->scheduleUpdate();
	}

	public function getName(){
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Monster Spawner";
	}

	public function hasName(){
		return isset($this->namedtag->CustomName);
	}

	public function setName($str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}

	public function canUpdate() : bool{
		if($this->getData() === 0) return false;
		$hasPlayer = false;
		$count = 0;
		foreach($this->getLevel()->getEntities() as $e){
			if($e instanceof Player){
				if($e->distance($this->getBlock()) <= 15) $hasPlayer = true;
			}
			if($e::NETWORK_ID == $this->getData()) $count ++;
		}
		if($hasPlayer and $count < 15) return true; // Spawn limit = 15
		return false;
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$this->timings->startTiming();

		if(!($this->chunk instanceof FullChunk)) return false;
		if($this->canUpdate()){
			$currentTick = $this->getLevel()->getServer()->getTick();
			$baseTick = $this->getLevel()->getServer()->getTicksPerSecondAverage();
			if(($currentTick - $this->lastUpdate) > $baseTick * 10){//Spawn per 10 seconds
				$this->lastUpdate = $currentTick;
				$up = $this->getLevel()->getBlock($this->getSide(Vector3::SIDE_UP));
				if($up->getId() == Item::AIR){
					$nbt = new CompoundTag("", [
						"Pos" => new EnumTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y + 1),
							new DoubleTag("", $this->z)
						]),
						"Motion" => new EnumTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						"Rotation" => new EnumTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]);
					$entity = Entity::createEntity($this->getData(), $this->chunk, $nbt);
					$entity->spawnToAll();
				}
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::MOB_SPAWNER),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new IntTag("data", (int) $this->getData())
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}
