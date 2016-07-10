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

use pocketmine\item\Item;

class BaseTransaction implements Transaction{
	/** @var Inventory */
	protected $inventory;
	/** @var int */
	protected $slot;
	/** @var Item */
	protected $sourceItem;
	/** @var Item */
	protected $targetItem;
	/** @var float */
	protected $creationTime;
	/** @var int */
	protected $failures = 0;

	/**
	 * @param Inventory $inventory
	 * @param int       $slot
	 * @param Item      $sourceItem
	 * @param Item      $targetItem
	 */
	public function __construct(Inventory $inventory, $slot, Item $sourceItem, Item $targetItem){
		$this->inventory = $inventory;
		$this->slot = (int) $slot;
		$this->sourceItem = clone $sourceItem;
		$this->targetItem = clone $targetItem;
		$this->creationTime = microtime(true);
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getSlot(){
		return $this->slot;
	}

	public function getSourceItem(){
		return clone $this->sourceItem;
	}

	public function getTargetItem(){
		return clone $this->targetItem;
	}
	
	public function setSourceItem(Item $item){
		$this->sourceItem = clone $item;
	}
	
	public function setTargetItem(Item $item){
		$this->targetItem = clone $item;
	}
	
	public function getFailures(){
		return $this->failures;
	}
	
	public function addFailure(){
		$this->failures++;
	}
	
	/**
	 * Returns the change in inventory resulting from this transaction
	 * @return Item[
	 *				"in" => items added to the inventory
	 *				"out" => items removed from the inventory
	 * ]
	 */
	public function getChange(){
		
		if($this->sourceItem->deepEquals($this->targetItem, true, true, true)){
			//This should never happen, somehow a change happened where nothing changed
			return null;
			
		}elseif($this->sourceItem->deepEquals($this->targetItem)){ //Same item, change of count
			$item = clone $this->sourceItem;
		
			$countDiff = $this->targetItem->getCount() - $this->sourceItem->getCount();
			
			$item->setCount(abs($countDiff));
			echo "Slot count changed by ".$countDiff."\n";
			
			if($countDiff < 0){	//Count decreased
				return ["in" => null,
						"out" => $item];
			}elseif($countDiff > 0){ //Count increased
				return [
						"in" => $item,
						"out" => null];
			}else{
				//Should be impossible (identical items and no count change)
				//This should be caught by the first condition even if it was possible, so it's safe enough to...
				echo "Wow, you broke the code\n";
				return null; 
			}
		}elseif($this->sourceItem->getId() !== Item::AIR and $this->targetItem->getId() === Item::AIR){
			//Slot emptied
			//return the item removed
			return ["in" => null,
					"out" => clone $this->sourceItem];
			
		}elseif($this->sourceItem->getId() === Item::AIR and $this->targetItem->getId() !== Item::AIR){
			//Slot filled with a new item (item added)
			return ["in" => clone $this->targetItem,
					"out" => null];

		}elseif(!$this->sourceItem->deepEquals($this->targetItem, false) and $this->sourceItem->canBeDamaged()){
			//Tool/armour damage change, no inventory change to speak of (not really)
			return null;
			
		}else{
			//Some other slot change - an item swap or a non-tool/armour meta change
			return ["in" => clone $this->targetItem, 
					"out" => clone $this->sourceItem];
		}
		//Don't remove this comment until you're sure there's nothing missing.
	}
}