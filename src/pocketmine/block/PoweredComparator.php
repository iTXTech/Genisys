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

namespace pocketmine\block;

use pocketmine\inventory\ComparableViewer;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\RedstoneUtil;

//TODO: finish
class PoweredComparator extends Transparent implements RedstoneSource, RedstoneTarget{
	protected $id = self::POWERED_COMPARATOR_BLOCK;

	const TICK_DELAY = 10;

	private static $updateQueue = [];
	private static $inputs = [];

	public function __construct($meta = 0){
		$this->meta = $meta;

		if(count(self::$updateQueue) === 0){
			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_NORTH];
			for($i = 0; $i < 4; $i++){
				self::$updateQueue[$i] = $sides[$i];
			}
		}

		if(count(self::$inputs) === 0){
			self::$inputs = [Block::HOPPER_BLOCK, Block::CHEST, Block::TRAPPED_CHEST, Block::DISPENSER, Block::BREWING_STAND_BLOCK, Block::FURNACE, Block::BURNING_FURNACE];
		}
	}

	public function getHardness(){
		return 0;
	}

	public function getResistance(){
		return 0;
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getLightLevel(){
		return $this->isPowered() ? 9 : 0;
	}

	public function isPowered() :bool{
		return $this->id == self::POWERED_COMPARATOR_BLOCK;
	}

	public function getDrops(Item $item) : array{
		return [
			[Block::POWERED_COMPARATOR_BLOCK, 0 ,1]
		];
	}

	public function setPowered(bool $powered){
		if($this->isPowered() != $powered){
			$this->id = $powered ? Block::POWERED_COMPARATOR_BLOCK : Block::UNPOWERED_REPEATER_BLOCK;
			$this->getLevel()->setBlock($this, $this, false, true);
		}
	}

	public function getDirection() : int{
		$faces = [
			3 => 2,
			0 => 3,
			1 => 0,
			2 => 1,
		];
		return $faces[$this->meta];
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player !== null){
			$faces = [
				2 => 3,
				3 => 0,
				0 => 1,
				1 => 2,
			];
			$this->meta = $faces[$player != null ? $player->getDirection() : 0];
		}
		$input = $this->getSide(Vector3::getOppositeSide($this->getDirection()));
		if(in_array($input->getId(), self::$inputs)){
			$tile = $this->getLevel()->getTile($input);
			if($tile instanceof InventoryHolder){
				$inventory = $tile->getInventory();
				$viewed = false;
				foreach($inventory->getViewers() as $viewer){
					if($viewer instanceof ComparableViewer){
						$viewed = true;
						$viewer->addViewer($this);
						break;
					}
				}
				if(!$viewed){
					$inventory->addViewer(new ComparableViewer($this, $inventory));
				}
			}
		}
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	public function onBreak(Item $item){
		$input = $this->getSide(Vector3::getOppositeSide($this->getDirection()));
		if(in_array($input->getId(), self::$inputs)){
			$tile = $this->getLevel()->getTile($input);
			$cViewer = null;
			if($tile instanceof InventoryHolder){
				$inventory = $tile->getInventory();
				foreach($inventory->getViewers() as $viewer){
					if($viewer instanceof ComparableViewer){
						$cViewer = $viewer;
					}
				}
				if($cViewer != null){
					$cViewer->removeViewer($this);
				}
			}
		}
		return parent::onBreak($item);
	}

	public function onActivate(Item $item, Player $player = null){
		$this->setPowered(!$this->isPowered());
		return true;
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$receiving = $this->isReceivingPower();
			if(!$this->isPowered() != $receiving){
				$this->getLevel()->setBlockCache($this, $receiving);
				$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
			}
		}
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			$receiving = $this->isReceivingPower();
			if($this->getLevel()->getBlockCache($this) === true){
				if(!$this->isPowered()){
					$this->setPowered(true);
				}
			}
		}
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return 0;
	}

	public function isReceivingPower() : bool{
		$face = Vector3::getOppositeSide($this->getDirection());
		return RedstoneUtil::isEmittingPower($this->getSide($face), $this->getDirection());
	}
}