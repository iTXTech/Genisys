<?php

/*
 *
<<<<<<< HEAD
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\tile;

use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class ItemFrame extends Spawnable{

	public function __construct(Chunk $chunk, CompoundTag $nbt) {
		if(!isset($nbt->Item) or !($nbt->Item instanceof CompoundTag)) {
			$nbt->Item = Item::get(Item::AIR)->nbtSerialize(-1, "Item");
		}
		parent::__construct($chunk, $nbt);
	}

	public function getName() : string{
		return "Item Frame";
	}

	public function getItemRotation() {
		return $this->namedtag["ItemRotation"];
	}

	public function hasItem() : bool{
		return $this->getItem()->getId() !== Item::AIR;
	}

	public function setItemRotation(int $itemRotation){
		$this->namedtag->ItemRotation = new ByteTag("ItemRotation", $itemRotation);
		$this->onChanged();
	}

	public function getItem(){
		return Item::nbtDeserialize($this->namedtag->Item);
	}

	public function setItem(Item $item){
		$this->namedtag->Item = $item->nbtSerialize(-1, "Item");
		$this->onChanged();
	}

	public function getItemDropChance(){
		return $this->namedtag["ItemDropChance"];
	}

	public function setItemDropChance(float $chance = 1.0){
		$this->namedtag->ItemDropChance = new FloatTag("ItemDropChance", $chance);
	}

	public function getSpawnCompound() {
		if(!isset($this->namedtag->Item)) {
			$this->setItem(Item::get(Item::AIR), false);
		}
		/** @var CompoundTag $nbtItem */
		$nbtItem = clone $this->namedtag->Item;
		$nbtItem->setName("Item");
		if($nbtItem["id"] == 0) {
			return new CompoundTag("", [
				new StringTag("id", Tile::ITEM_FRAME),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z),
				new ByteTag("ItemRotation", 0),
				new FloatTag("ItemDropChance", (float) $this->getItemDropChance())
			]);
		} else {
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