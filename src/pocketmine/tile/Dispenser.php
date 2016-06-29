<?php

/*
 *
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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\inventory\DispenserInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\entity\Egg;
use pocketmine\entity\ThrownExpBottle;
use pocketmine\entity\ThrownPotion;
use pocketmine\entity\Arrow;
use pocketmine\entity\Snowball;


use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;

use pocketmine\nbt\tag\StringTag;

class Dispenser extends Spawnable implements InventoryHolder, Container, Nameable{

	/** @var DispenserInventory */
	protected $inventory;

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new DispenserInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}

		$this->scheduleUpdate();
	}

	public function close(){
		if($this->closed === false){
			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}

			foreach($this->getInventory()->getViewers() as $player){
				$player->removeWindow($this->getInventory());
			}
			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items = new ListTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 9;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return NBT::getItemHelper($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = NBT::putItemHelper($item, $index);

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return DispenserInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Dispenser";
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

	public function getMotion(){
		$meta = $this->getBlock()->getDamage();
		switch($meta){
			case Vector3::SIDE_DOWN:
				return [0, -1, 0];
			case Vector3::SIDE_UP:
				return [0, 1, 0];
			case Vector3::SIDE_NORTH:
				return [0, 0, -1];
			case Vector3::SIDE_SOUTH:
				return [0, 0, 1];
			case Vector3::SIDE_WEST:
				return [-1, 0, 0];
			case Vector3::SIDE_EAST:
				return [1, 0, 0];
			default:
				return [0, 0, 0];
		}
	}

	public function activate(){
		$itemIndex = [];
		for($i = 0; $i < $this->getSize(); $i++){
			$item = $this->getInventory()->getItem($i);
			if($item->getId() != Item::AIR){
				$itemIndex[] = [$i, $item];
			}
		}
		$max = count($itemIndex) - 1;
		if($max < 0) $itemArr = null;
		elseif($max == 0) $itemArr = $itemIndex[0];
		else $itemArr = $itemIndex[mt_rand(0, $max)];

		if(is_array($itemArr)){
			/** @var Item $item */
			$item = $itemArr[1];
			$item->setCount($item->getCount() - 1);
			$this->getInventory()->setItem($itemArr[0], $item->getCount() > 0 ? $item : Item::get(Item::AIR));
			$motion = $this->getMotion();
			$needItem = Item::get($item->getId(), $item->getDamage());
			$f = 1.5;
			switch($needItem->getId()){
				case Item::ARROW:
					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
						"Fire" => new ShortTag("Fire", 0)
					]);

					$arrow = Entity::createEntity("Arrow", $this->chunk, $nbt);

					$arrow->setMotion($arrow->getMotion()->multiply($f));
					$arrow->spawnToAll();

					break;
				case Item::SNOWBALL:
					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
					]);

					$snowball = Entity::createEntity("Snowball", $this->chunk, $nbt);

					$snowball->setMotion($snowball->getMotion()->multiply($f));
					$snowball->spawnToAll();

					break;
				case Item::EGG:
					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
					]);

					$egg = Entity::createEntity("Egg", $this->chunk, $nbt);

					$egg->setMotion($egg->getMotion()->multiply($f));
					$egg->spawnToAll();

					break;
				case Item::SPLASH_POTION:
					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
						"PotionId" => new ShortTag("PotionId", $item->getDamage()),
					]);

					$thrownPotion = Entity::createEntity("ThrownPotion", $this->chunk, $nbt);

					$thrownPotion->setMotion($thrownPotion->getMotion()->multiply($f));
					$thrownPotion->spawnToAll();

					break;
				case Item::ENCHANTING_BOTTLE:
					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
					]);

					$thrownExpBottle = Entity::createEntity("ThrownExpBottle", $this->chunk, $nbt);

					$thrownExpBottle->setMotion($thrownExpBottle->getMotion()->multiply($f));
					$thrownExpBottle->spawnToAll();

					break;
				default:
					$itemTag = NBT::putItemHelper($needItem);
					$itemTag->setName("Item");

					$nbt = new CompoundTag("", [
						"Pos" => new ListTag("Pos", [
							new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
							new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
							new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
						]),
						"Motion" => new ListTag("Motion", [
							new DoubleTag("", $motion[0]),
							new DoubleTag("", $motion[1]),
							new DoubleTag("", $motion[2])
						]),
						"Rotation" => new ListTag("Rotation", [
							new FloatTag("", lcg_value() * 360),
							new FloatTag("", 0)
						]),
						"Health" => new ShortTag("Health", 5),
						"Item" => $itemTag,
						"PickupDelay" => new ShortTag("PickupDelay", 10)
					]);

					$f = 0.3;
					$itemEntity = new ItemEntity($this->chunk, $nbt, $this);
					$itemEntity->setMotion($itemEntity->getMotion()->multiply($f));
					$itemEntity->spawnToAll();
					break;
			}

			for($i = 1; $i < 10; $i++){
				$this->getLevel()->addParticle(new SmokeParticle($this->add($motion[0] * $i * 0.3 + 0.5, $motion[1] == 0 ? 0.5 : $motion[1] * $i * 0.3, $motion[2] * $i * 0.3 + 0.5)));
			}
		}
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::DISPENSER),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}