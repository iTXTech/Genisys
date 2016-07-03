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

/**
 * Manages crafting operations
 * This class includes future methods for shaped crafting
 *
 * TODO: add small matrix inventory
 */
class CraftingInventory extends BaseInventory{

	/** @var Item */
	private $resultItem;

	/**
	 * @param InventoryHolder $holder
	 * @param InventoryType   $inventoryType
	 *
	 * @throws \Throwable
	 */
	public function __construct(InventoryHolder $holder, InventoryType $inventoryType){
		if($inventoryType->getDefaultTitle() !== "Crafting"){
			throw new \InvalidStateException("Invalid Inventory type, expected CRAFTING or WORKBENCH");
		}
		parent::__construct($holder, $inventoryType);
	}
	
	public function setResultItem(Item $item){
		$this->resultItem = $item;
	}
	
	/**
	 * @return Item
	 */
	public function getResultItem(){
		return $this->resultItem;
	}
}