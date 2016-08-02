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
use pocketmine\Player;

class BaseTransaction implements Transaction{
	/** @var Inventory */
	protected $inventory;
	/** @var int */
	protected $slot;
	/** @var Item */
	protected $targetItem;
	/** @var float */
	protected $creationTime;
	/** @var int */
	protected $transactionType = Transaction::TYPE_NORMAL;
	/** @var int */
	protected $failures = 0;
	/** @var bool */
	protected $wasSuccessful = false;

	/**
	 * @param Inventory|null $inventory
	 * @param int|null       $slot
	 * @param Item           $targetItem
	 * @param int            $transactionType
	 */
	public function __construct($inventory, $slot, Item $targetItem, $transactionType = Transaction::TYPE_NORMAL){
		$this->inventory = $inventory;
		$this->slot = (int) $slot;
		$this->targetItem = clone $targetItem;
		$this->creationTime = microtime(true);
		$this->transactionType = $transactionType;
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
	
	public function getTargetItem(){
		return clone $this->targetItem;
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
	
	public function succeeded(){
		return $this->wasSuccessful;
	}
	
	public function setSuccess($value = true){
		$this->wasSuccessful = $value;
	}
	
	public function getTransactionType(){
		return $this->transactionType;
	}
	
	/**
	 * @param Player $source
	 *
	 * Sends a slot update to inventory viewers, excepting those specified as parameters.
	 * For successful transactions, the source does not need to be updated, only the viewers
	 * For failed transactions, only the source needs to be updated, and not the viewers.
	 */
	public function sendSlotUpdate(Player $source){
		//If the transaction was successful, send updates _only_ to non-instigators
		//If it failed, send updates _only_ to the instigator.
		if($this->getTransactionType() === Transaction::TYPE_DROP_ITEM){
			//Do not send updates for drop item transactions as there is nothing to update
			return;
		}
		
		if($this->getInventory() instanceof TemporaryInventory){
			//Attempting to change anvil slots server-side causes PE to crash.
			return;
		}
		
		$targets = [];
		if($this->wasSuccessful){
			$targets = $this->getInventory()->getViewers();
			foreach($targets as $hash => $t){
				if($t === $source){
					unset($targets[$hash]); //Remove the source player from the list of players to update
				}
			}
		}else{
			$targets = [$source];
		}
		$this->inventory->sendSlot($this->slot, $targets);
	}
	
	/**
	 * Returns the change in inventory resulting from this transaction
	 * @return Item[
	 *				"in" => items added to the inventory
	 *				"out" => items removed from the inventory
	 * ]
	 */
	public function getChange(){
		$sourceItem = $this->getInventory()->getItem($this->slot);
		
		if($sourceItem->deepEquals($this->targetItem, true, true, true)){
			//This should never happen, somehow a change happened where nothing changed
			return null;
			
		}elseif($sourceItem->deepEquals($this->targetItem)){ //Same item, change of count
			$item = clone $sourceItem;
			$countDiff = $this->targetItem->getCount() - $sourceItem->getCount();
			$item->setCount(abs($countDiff));
			
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
		}elseif($sourceItem->getId() !== Item::AIR and $this->targetItem->getId() === Item::AIR){
			//Slot emptied
			//return the item removed
			return ["in" => null,
					"out" => clone $sourceItem];
			
		}elseif($sourceItem->getId() === Item::AIR and $this->targetItem->getId() !== Item::AIR){
			//Slot filled with a new item (item added)
			return ["in" => $this->getTargetItem(),
					"out" => null];

		}else{
			//Some other slot change - an item swap (tool damage changes will be ignored as they are processed server-side before any change is sent by the client
			return ["in" => $this->getTargetItem(), 
					"out" => clone $sourceItem];
		}
		//Don't remove this comment until you're sure there's nothing missing.
	}
	
	
	public function execute(Player $source): bool{
		
		//How to do this... When the inventory is a temporary inventory, we want to recalculate all slots
		//When it's a normal inventory, do whatever
		if($this->getInventory()->processSlotChange($this)){ //This means that the transaction should be handled the normal way
			if(!$source->getServer()->allowInventoryCheats){
				$change = $this->getChange();
				
				if($change["out"] instanceof Item){
					if($this->getInventory()->slotContains($this->getSlot(), $change["out"]) and !$source->isCreative()){
						//Do not add items to the crafting inventory in creative to prevent weird duplication bugs.
						$source->getFloatingInventory()->addItem($change["out"]);

					}elseif(!$source->isCreative()){ //Transaction failed, if the player is not in creative then this needs to be retried.
						return false;
					}
				}
				if($change["in"] instanceof Item){
					if($source->getFloatingInventory()->contains($change["in"]) and !$source->isCreative()){
						$source->getFloatingInventory()->removeItem($change["in"]);
						
					}elseif(!$source->isCreative()){ //Transaction failed, if the player was not creative then transaction is illegal
						return false;
					}
				}
			}
			$this->getInventory()->setItem($this->getSlot(), $this->getTargetItem(), false);
		}
		return true;
	}
}