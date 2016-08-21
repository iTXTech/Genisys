<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\Player;
use pocketmine\Server;

class PlayerInventory extends BaseInventory{

	protected $itemInHandIndex = 0;
	/** @var int[] */
	protected $hotbar;

	public function __construct(Human $player, $contents = null){
		$this->hotbar = range(0, $this->getHotbarSize() - 1, 1);
		parent::__construct($player, InventoryType::get(InventoryType::PLAYER));

		if($contents !== null){
			if($contents instanceof ListTag){ //Saved data to be loaded into the inventory
				foreach($contents as $item){
					if($item["Slot"] >= 0 and $item["Slot"] < $this->getHotbarSize()){ //Hotbar
						if(isset($item["TrueSlot"])){

							//Valid slot was found, change the linkage to this slot
							if(0 <= $item["TrueSlot"] and $item["TrueSlot"] < $this->getSize()){
								$this->hotbar[$item["Slot"]] = $item["TrueSlot"];

							}elseif($item["TrueSlot"] < 0){ //Link to an empty slot (empty hand)
								$this->hotbar[$item["Slot"]] = -1;
							}
						}
						/* If TrueSlot is not set, leave the slot index as its default which was filled in above
						 * This only overwrites slot indexes for valid links */
					}elseif($item["Slot"] >= 100 and $item["Slot"] < 104){ //Armor
						$this->setItem($this->getSize() + $item["Slot"] - 100, NBT::getItemHelper($item), false);
					}else{
						$this->setItem($item["Slot"] - $this->getHotbarSize(), NBT::getItemHelper($item), false);
					}
				}
			}else{
				throw new \InvalidArgumentException("Expecting ListTag, received ".gettype($contents));
			}
		}
	}

	public function getSize(){
		return parent::getSize() - 4; //Remove armor slots
	}

	public function setSize($size){
		parent::setSize($size + 4);
		$this->sendContents($this->getViewers());
	}

	/**
	 * @param int $index
	 *
	 * @return int
	 *
	 * Returns the index of the inventory slot linked to the specified hotbar slot
	 */
	public function getHotbarSlotIndex($index){
		return ($index >= 0 and $index < $this->getHotbarSize()) ? $this->hotbar[$index] : -1;
	}

	/**
	 * @param int $index
	 * @param int $slot
	 *
	 * Move item to hotbar slot. Compatible with old method
	 */
	public function setHotbarSlotIndex($index, $slot){
		$i1 = $this->getItem($index);
		$i2 = $this->getItem($slot);
		$this->setItem($index, $i2);
		$this->setItem($slot, $i1);
	}

	/**
	 * @return int
	 *
	 * Returns the index of the inventory slot the player is currently holding
	 */
	public function getHeldItemIndex(){
		return $this->itemInHandIndex;
	}

	/**
	 * @param int  $hotbarSlotIndex
	 * @param bool $sendToHolder
	 * @param int  $slotMapping
	 *
	 * Sets which hotbar slot the player is currently holding.
	 * Allows slot remapping as specified by a MobEquipmentPacket. DO NOT CHANGE SLOT MAPPING IN PLUGINS!
	 * This new implementation is fully compatible with older APIs.
	 * NOTE: Slot mapping is the raw slot index sent by MCPE, which will be between 9 and 44.
	 */
	public function setHeldItemIndex($hotbarSlotIndex, $sendToHolder = true, $slotMapping = null){
		if($slotMapping !== null){
			//Get the index of the slot in the actual inventory
			$slotMapping -= $this->getHotbarSize();
		}
		if(0 <= $hotbarSlotIndex and $hotbarSlotIndex < $this->getHotbarSize()){
			$this->itemInHandIndex = $hotbarSlotIndex;
			if($slotMapping !== null){
				/* Handle a hotbar slot mapping change. This allows PE to select different inventory slots.
				 * This is the only time slot mapping should ever be changed. */

				if($slotMapping < 0 or $slotMapping >= $this->getSize()){
					//Mapping was not in range of the inventory, set it to -1
					//This happens if the client selected a blank slot (sends 255)
					$slotMapping = -1;
				}

				$item = $this->getItem($slotMapping);
				if($this->getHolder() instanceof Player){
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this->getHolder(), $item, $slotMapping, $hotbarSlotIndex));
					if($ev->isCancelled()){
						$this->sendHeldItem($this->getHolder());
						$this->sendContents($this->getHolder());
						return;
					}
				}

				if(($key = array_search($slotMapping, $this->hotbar)) !== false and $slotMapping !== -1){
					/* Do not do slot swaps if the slot was null
					 * Chosen slot is already linked to a hotbar slot, swap the two slots around.
					 * This will already have been done on the client-side so no changes need to be sent. */
					$this->hotbar[$key] = $this->hotbar[$this->itemInHandIndex];
				}

				$this->hotbar[$this->itemInHandIndex] = $slotMapping;
			}
			$this->sendHeldItem($this->getHolder()->getViewers());
			if($sendToHolder){
				$this->sendHeldItem($this->getHolder());
			}
		}
	}

	/**
	 * @return Item
	 *
	 * Returns the item the player is currently holding
	 */
	public function getItemInHand(){
		$item = $this->getItem($this->getHeldItemSlot());
		if($item instanceof Item){
			return $item;
		}else{
			return Item::get(Item::AIR, 0, 0);
		}
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 *
	 * Sets the item in the inventory slot the player is currently holding.
	 */
	public function setItemInHand(Item $item){
		return $this->setItem($this->getHeldItemSlot(), $item);
	}

	/**
	 * @return int[]
	 *
	 * Returns an array of hotbar indices
	 */
	public function getHotbar(){
		return $this->hotbar;
	}

	/**
	 * @return int
	 *
	 * Returns the inventory slot index of the currently equipped slot
	 */
	public function getHeldItemSlot(){
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	/**
	 * @param int $slot
	 */
	public function setHeldItemSlot($slot){
		$this->setHotbarSlotIndex($this->getHeldItemSlot(), $slot);
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target){
		$item = $this->getItemInHand();

		$pk = new MobEquipmentPacket();
		$pk->eid = ($target === $this->getHolder() ? 0 : $this->getHolder()->getId());
		$pk->item = $item;
		$pk->slot = $this->getHeldItemSlot();
		$pk->selectedSlot = $this->getHeldItemIndex();

		if(!is_array($target)){
			$target->dataPacket($pk);
			if($target === $this->getHolder()){
				$this->sendSlot($this->getHeldItemSlot(), $target);
			}
		}else{
			Server::broadcastPacket($target, $pk);
			foreach($target as $player){
				if($player === $this->getHolder()){
					$this->sendSlot($this->getHeldItemSlot(), $player);
					break;
				}
			}
		}
	}

	public function onSlotChange($index, $before, $send){
		if($send){
			$holder = $this->getHolder();
			if(!$holder instanceof Player or !$holder->spawned){
				return;
			}

			parent::onSlotChange($index, $before, $send);
		}
		if($index === $this->itemInHandIndex){
			$this->sendHeldItem($this->getHolder()->getViewers());
			if($send){
				$this->sendHeldItem($this->getHolder());
			}

		}elseif($index >= $this->getSize()){ //Armour equipment
			$this->sendArmorSlot($index, $this->getViewers());
			$this->sendArmorSlot($index, $this->getHolder()->getViewers());
		}
	}

	public function getHotbarSize(){
		return 9;
	}

	public function getArmorItem($index){
		return $this->getItem($this->getSize() + $index);
	}

	public function setArmorItem($index, Item $item){
		return $this->setItem($this->getSize() + $index, $item);
	}

	public function getHelmet(){
		return $this->getItem($this->getSize());
	}

	public function getChestplate(){
		return $this->getItem($this->getSize() + 1);
	}

	public function getLeggings(){
		return $this->getItem($this->getSize() + 2);
	}

	public function getBoots(){
		return $this->getItem($this->getSize() + 3);
	}

	public function setHelmet(Item $helmet){
		return $this->setItem($this->getSize(), $helmet);
	}

	public function setChestplate(Item $chestplate){
		return $this->setItem($this->getSize() + 1, $chestplate);
	}

	public function setLeggings(Item $leggings){
		return $this->setItem($this->getSize() + 2, $leggings);
	}

	public function setBoots(Item $boots){
		return $this->setItem($this->getSize() + 3, $boots);
	}

	public function setItem($index, Item $item, $send = true){
		if($index < 0 or $index >= $this->size){
			return false;
		}elseif($item->getId() === 0 or $item->getCount() <= 0){
			return $this->clear($index, $send);
		}

		if($index >= $this->getSize()){ //Armor change
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled() and $this->getHolder() instanceof Human){
				$this->sendArmorSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}else{
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}


		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old, $send);

		return true;
	}

	public function clear($index, $send = true){
		if(isset($this->slots[$index])){
			$item = Item::get(Item::AIR, null, 0);
			$old = $this->slots[$index];
			if($index >= $this->getSize() and $index < $this->size){ //Armor change
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}else{
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getId() !== Item::AIR){
				$this->slots[$index] = clone $item;
			}else{
				unset($this->slots[$index]);
			}

			$this->onSlotChange($index, $old, $send);
		}

		return true;
	}

	/**
	 * @return Item[]
	 */
	public function getArmorContents(){
		$armor = [];

		for($i = 0; $i < 4; ++$i){
			$armor[$i] = $this->getItem($this->getSize() + $i);
		}

		return $armor;
	}

	public function clearAll(){
		$limit = $this->getSize() + 4;
		for($index = 0; $index < $limit; ++$index){
			$this->clear($index, false);
		}
		$this->hotbar = range(0, $this->getHotbarSize() - 1, 1);
		$this->sendContents($this->getViewers());
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendArmorContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new ContainerSetContentPacket();
				$pk2->windowid = ContainerSetContentPacket::SPECIAL_ARMOR;
				$pk2->slots = $armor;
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 * @param Item[] $items
	 */
	public function setArmorContents(array $items){
		for($i = 0; $i < 4; ++$i){
			if(!isset($items[$i]) or !($items[$i] instanceof Item)){
				$items[$i] = Item::get(Item::AIR, null, 0);
			}

			if($items[$i]->getId() === Item::AIR){
				$this->clear($this->getSize() + $i);
			}else{
				$this->setItem($this->getSize() + $i, $items[$i]);
			}
		}
	}


	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendArmorSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				$pk2 = new ContainerSetSlotPacket();
				$pk2->windowid = ContainerSetContentPacket::SPECIAL_ARMOR;
				$pk2->slot = $index - $this->getSize();
				$pk2->item = $this->getItem($index);
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetContentPacket();
		$pk->slots = [];
		for($i = 0; $i < $this->getSize(); ++$i){ //Do not send armor by error here
			$pk->slots[$i] = $this->getItem($i);
		}

		//Because PE is stupid and shows 9 less slots than you send it, give it 9 dummy slots so it shows all the REAL slots.
		for($i = $this->getSize(); $i < $this->getSize() + $this->getHotbarSize(); ++$i){
			$pk->slots[$i] = Item::get(Item::AIR, 0, 0);
		}

		foreach($target as $player){
			$pk->hotbar = [];
			if($player === $this->getHolder()){
				for($i = 0; $i < $this->getHotbarSize(); ++$i){
					$index = $this->getHotbarSlotIndex($i);
					$pk->hotbar[$i] = $index <= -1 ? -1 : $index + $this->getHotbarSize();
				}
			}
			if(($id = $player->getWindowId($this)) === -1 or $player->spawned !== true){
				$this->close($player);
				continue;
			}
			$pk->windowid = $id;
			$player->dataPacket(clone $pk);
		}
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetSlotPacket();
		$pk->slot = $index;
		$pk->item = clone $this->getItem($index);

		foreach($target as $player){
			if($player === $this->getHolder()){
				/** @var Player $player */
				$pk->windowid = 0;
				$player->dataPacket(clone $pk);
			}else{
				if(($id = $player->getWindowId($this)) === -1){
					$this->close($player);
					continue;
				}
				$pk->windowid = $id;
				$player->dataPacket(clone $pk);
			}
		}
	}

	/**
	 * @return Human|Player
	 */
	public function getHolder(){
		return parent::getHolder();
	}

}
