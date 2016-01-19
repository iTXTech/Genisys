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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\level\format\FullChunk;

class MobSpawner extends Spawnable implements Nameable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Data)){
			$nbt->Data = new IntTag("Data", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getData(){
		return $this->namedtag["Data"];
	}

	public function setData($data){
		$this->namedtag->Data = new IntTag("Data", $data);
		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getName(){
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Mob Spawner";
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

	public function onUpdate(){
		return false;
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
