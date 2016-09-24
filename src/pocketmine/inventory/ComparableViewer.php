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

namespace pocketmine\inventory;

use pocketmine\block\Block;
use pocketmine\block\IndirectRedstoneSource;
use pocketmine\item\Item;
use pocketmine\level\Level;

class ComparableViewer implements InventoryViewer{
	/** @var Block[] */
	private $viewingBlocks = [];
	/** @var Inventory */
	private $viewed;
	private $maxItemValue;
	private $currentValue = 0;
	private $redstonePower = 0;

	public function __construct(Block $block, Inventory $inventory){
		$this->viewed = $inventory;
		$this->viewingBlocks[Level::blockHash($block->x, $block->y, $block->z)] = $block;

		$this->maxItemValue = $inventory->getSize() * 64;

		for($i = 0; $i < $inventory->getSize(); ++$i){
			$slot = $inventory->getItem($i);
			if($slot->getId() !== Block::AIR and $slot->getCount() > 0){
				$this->currentValue += $this->getItemValue($slot);
			}
		}
		$this->updateRedstoneValue();
		$inventory->addViewer($this);
	}

	public function onSlotSet(Inventory $inventory, int $slot, Item $item, Item $before){
		$this->currentValue -= $this->getItemValue($before);
		$this->currentValue += $this->getItemValue($item);
		$this->updateRedstoneValue();
	}

	private function getItemValue(Item $item) : int{
		return ($item->getCount() * 64) / $item->getMaxStackSize();
	}

	private function updateRedstoneValue(){
		$this->redstonePower = (1 + ($this->currentValue / $this->maxItemValue) * (IndirectRedstoneSource::REDSTONE_POWER_MAX - 1));
	}

	public function getRedstonePower() : int{
		return $this->redstonePower;
	}

	public function addViewer(Block $block){
		$this->viewingBlocks[Level::blockHash($block->x, $block->y, $block->z)] = $block;
	}

	public function removeViewer(Block $block){
		if(isset($this->viewingBlocks[Level::blockHash($block->x, $block->y, $block->z)])){
			unset($this->viewingBlocks[Level::blockHash($block->x, $block->y, $block->z)]);
			if(count($this->viewingBlocks) === 0){
				$this->viewed->removeViewer($this);
			}
		}
	}
}