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

use pocketmine\block\Hopper as HopperBlock;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\inventory\HopperInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Hopper extends Spawnable implements InventoryHolder, Container, Nameable{
	/** @var HopperInventory */
	protected $inventory;

	/** @var bool */
	protected $isLocked = false;
	
	/** @var bool */
	protected $isPowered = false;

	public function __construct(Chunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->inventory = new HopperInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}
		$this->namedtag->TransferCooldown = new IntTag("TransferCooldown", 0);

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
	
	public function activate(){
		$this->isPowered = true;
	}
	
	public function deactivate(){
		$this->isPowered = false;
	}

	public function canUpdate(){
		return $this->namedtag->TransferCooldown->getValue() === 0 and !$this->isPowered;
	}

	public function resetCooldownTicks(){
		$this->namedtag->TransferCooldown->setValue(8);
	}

	public function onUpdate(){
		if(!($this->getBlock() instanceof HopperBlock)){
			return false;
		}
		//Pickup dropped items
		//This can happen at any time regardless of cooldown
		$area = clone $this->getBlock()->getBoundingBox(); //Area above hopper to draw items from
		$area->maxY = ceil($area->maxY) + 1; //Account for full block above, not just 1 + 5/8
		foreach($this->getLevel()->getChunkEntities($this->getBlock()->x >> 4, $this->getBlock()->z >> 4) as $entity){
			if(!($entity instanceof DroppedItem) or !$entity->isAlive()){
				continue;
			}
			if(!$entity->boundingBox->intersectsWith($area)){
				continue;
			}

			$item = $entity->getItem();
			if(!$item instanceof Item){
				continue;
			}
			if($item->getCount() < 1){
				$entity->kill();
				continue;
			}

			if($this->inventory->canAddItem($item)){
				$this->inventory->addItem($item);
				$entity->kill();
			}
		}

		if(!$this->canUpdate()){ //Hoppers only update CONTENTS every 8th tick
			$this->namedtag->TransferCooldown->setValue($this->namedtag->TransferCooldown->getValue() - 1);
			return true;
		}
		
		//Suck items from above tile inventories
		$source = $this->getLevel()->getTile($this->getBlock()->getSide(Vector3::SIDE_UP));
		if($source instanceof Tile and $source instanceof InventoryHolder){
			$inventory = $source->getInventory();
			$item = clone $inventory->getItem($inventory->firstOccupied());
			$item->setCount(1);
			if($this->inventory->canAddItem($item)){
				$this->inventory->addItem($item);
				$inventory->removeItem($item);
				$this->resetCooldownTicks();
				if($source instanceof Hopper){
					$source->resetCooldownTicks();
				}
			}
		}
		
		//Feed item into target inventory
		//Do not do this if there's a hopper underneath this hopper, to follow vanilla behaviour
		if(!($this->getLevel()->getTile($this->getBlock()->getSide(Vector3::SIDE_DOWN)) instanceof Hopper)){
			$target = $this->getLevel()->getTile($this->getBlock()->getSide($this->getBlock()->getDamage()));
			if($target instanceof Tile and $target instanceof InventoryHolder){
				$inv = $target->getInventory();
				foreach($this->inventory->getContents() as $item){
					if($item->getId() === Item::AIR or $item->getCount() < 1){
						continue;
					}
					$targetItem = clone $item;
					$targetItem->setCount(1);
					
					if($inv->canAddItem($targetItem)){
						$inv->addItem($targetItem);
						$this->inventory->removeItem($targetItem);
						$this->resetCooldownTicks();
						if($target instanceof Hopper){
							$target->resetCooldownTicks();
						}
						break;
					}
					
				}
			}
		}

		return true;
	}

	/**
	 * @return HopperInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 5;
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
			return Item::nbtDeserialize($this->namedtag->Items[$i]);
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
			$this->namedtag->Items[$i] = $item->nbtSerialize($index);
		}else{
			$this->namedtag->Items[$i] = $item->nbtSerialize($index);
		}

		return true;
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

	public function saveNBT(){
		$this->namedtag->Items = new ListTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Hopper";
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


	public function hasLock(){
		return isset($this->namedtag->Lock);
	}

	public function setLock(string $itemName = ""){
		if($itemName === ""){
			unset($this->namedtag->Lock);
			return;
		}
		$this->namedtag->Lock = new StringTag("Lock", $itemName);
	}
	
	public function checkLock(string $key){
		return $this->namedtag->Lock->getValue() === $key;
	}

	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::HOPPER),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}
		if($this->hasLock()){
			$c->Lock = $this->namedtag->Lock;
		}

		return $c;
	}
}
