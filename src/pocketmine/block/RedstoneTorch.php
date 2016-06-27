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

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\math\Vector3;

class RedstoneTorch extends RedstoneSource{

	protected $id = self::REDSTONE_TORCH;
	protected $ignore = "";

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getLightLevel(){
		return 7;
	}

	public function getLastUpdateTime(){
		return $this->getLevel()->getBlockTempData($this);
	}

	public function setLastUpdateTimeNow(){
		$this->getLevel()->setBlockTempData($this, $this->getLevel()->getServer()->getTick());
	}

	public function canCalcTurn(){
		if(!parent::canCalc()) return false;
		if($this->getLevel()->getServer()->getTick() != $this->getLastUpdateTime()) return true;
		return ($this->canScheduleUpdate() ? Level::BLOCK_UPDATE_SCHEDULED : false);
	}

	public function canScheduleUpdate(){
		return $this->getLevel()->getServer()->allowFrequencyPulse;
	}

	public function getFrequency(){
		return $this->getLevel()->getServer()->pulseFrequency;
	}

	public function getName() : string{
		return "Redstone Torch";
	}

	public function turnOn($ignore = ""){
		$result = $this->canCalcTurn();
		$this->setLastUpdateTimeNow();
		if($result === true){
			$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
			];
			$this->id = self::REDSTONE_TORCH;
			$this->getLevel()->setBlock($this, $this, true);
			$this->activateTorch([$faces[$this->meta]], [$ignore]);
			return true;
		}elseif($result === Level::BLOCK_UPDATE_SCHEDULED){
			$this->ignore = $ignore;
			$this->getLevel()->scheduleUpdate($this, 20 * $this->getFrequency());
			return true;
		}
		return false;
	}

	public function turnOff($ignore = ""){
		$result = $this->canCalcTurn();
		$this->setLastUpdateTimeNow();
		if($result === true){
			$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
			];
			$this->id = self::UNLIT_REDSTONE_TORCH;
			$this->getLevel()->setBlock($this, $this, true);
			$this->deactivateTorch([$faces[$this->meta]], [$ignore]);
			return true;
		}elseif($result === Level::BLOCK_UPDATE_SCHEDULED){
			$this->ignore = $ignore;
			$this->getLevel()->scheduleUpdate($this, 20 * $this->getFrequency());
			return true;
		}
		return false;
	}

	public function activateTorch(array $ignore = [], $notCheck = []){
		if($this->canCalc()){
			$this->activated = true;
			/** @var Door $block */

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
						$this->activateBlock($block);
					}
				}
			}
			//$this->lastUpdateTime = $this->getLevel()->getServer()->getTick();
		}
	}

	public function activate(array $ignore = []){
		$this->activateTorch($ignore);
	}

	public function deactivate(array $ignore = []){
		$this->deactivateTorch($ignore);
	}

	public function deactivateTorch(array $ignore = [], array $notCheck = []){
		if($this->canCalc()){
			$this->activated = false;
			/** @var Door $block */

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
						$this->deactivateBlock($block);
					}
				}
			}

			if(!in_array(Vector3::SIDE_DOWN, $ignore)){
				$block = $this->getSide(Vector3::SIDE_DOWN);
				if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
					if(!$this->checkPower($block)){
						/** @var $block ActiveRedstoneLamp */
						if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
					}

					$block = $this->getSide(Vector3::SIDE_DOWN, 2);
					$this->deactivateBlock($block);
				}
			}
			//$this->lastUpdateTime = $this->getLevel()->getServer()->getTick();
		}
	}

	public function onUpdate($type){
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$below = $this->getSide(0);
			$side = $this->getDamage();

			if($this->getSide($faces[$side])->isTransparent() === true and
				!($side === 0 and ($below->getId() === self::FENCE or
						$below->getId() === self::COBBLE_WALL
					))
			){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
			$this->activate([$faces[$side]]);
		}

		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->id == self::UNLIT_REDSTONE_TORCH) $this->turnOn($this->ignore);
			else $this->turnOff($this->ignore);
			return Level::BLOCK_UPDATE_SCHEDULED;
		}

		return false;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, false);
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		$this->deactivate([$faces[$this->meta]]);
		$this->getLevel()->setBlockTempData($this);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$below = $this->getSide(0);

		if($target->isTransparent() === false and $face !== 0){
			$faces = [
				1 => 5,
				2 => 4,
				3 => 3,
				4 => 2,
				5 => 1,
			];
			$this->meta = $faces[$face];
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}elseif(
			$below->isTransparent() === false or $below->getId() === self::FENCE or
			$below->getId() === self::COBBLE_WALL or
			$below->getId() == Block::INACTIVE_REDSTONE_LAMP or
			$below->getId() == Block::ACTIVE_REDSTONE_LAMP
		){
			$this->meta = 0;
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}

	public function getDrops(Item $item) : array{
		return [
			[Item::LIT_REDSTONE_TORCH, 0, 1],
		];
	}

	public function isActivated(Block $from = null){
		return true;
	}
}
