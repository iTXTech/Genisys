<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/31 16:10
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */

//Warning: DO NOT COPY IT UNTIL A STABLE VERSION OF MCPE 0.14.0 RELEASE!
namespace pocketmine\tile;

use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;

class ItemFrame extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Item)){
			$nbt->Item = NBT::putItemHelper(Item::get(Item::AIR, 0, 1));
		}
		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new ByteTag("ItemRotation", 0);
		}
		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new FloatTag("ItemDropChance", 1.0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getName() : string{
		return "Item Frame";
	}

	public function getItemRotation(){
		return $this->namedtag["ItemRotation"];
	}

	public function setItemRotation(int $itemRotation){
		$this->namedtag->ItemRotation = new ByteTag("ItemRotation", $itemRotation);
		$this->setChanged();
	}

	public function getItem(){
		return NBT::getItemHelper($this->namedtag->Item);
	}

	public function setItem(Item $item){
		$this->namedtag->Item = NBT::putItemHelper($item);
		$this->setChanged();
	}

	public function getItemDropChance(){
		return $this->namedtag["ItemDropChance"];
	}

	public function setItemDropChance($chance = 1.0){
		$this->namedtag->ItemDropChance = new FloatTag("ItemDropChance", $chance);
	}

	private function setChanged(){
		$this->spawnToAll();
		if($this->chunk instanceof FullChunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		$item = NBT::getItemHelper($this->namedtag->Item);
		/** @var CompoundTag $nbtItem */
		$nbtItem = clone $this->namedtag->Item;
		$nbtItem->setName("Item");
		if($item->getId() === 0){
			return new CompoundTag("", [
				new StringTag("id", Tile::ITEM_FRAME),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z),
				new ByteTag("ItemRotation", 0),
				new FloatTag("ItemDropChance", (float) $this->getItemDropChance())
			]);
		}else{
			return new CompoundTag("", [
				new StringTag("id", Tile::ITEM_FRAME),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z),
				$nbtItem,
				new ByteTag("ItemRotation", (int) $this->getItemRotation()),
				new FloatTag("ItemDropChance", (float) $this->getItemDropChance())
			]);
		}
	}
}