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
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\RedstoneUtil;

class PoweredRepeater extends Transparent implements RedstoneSource, RedstoneTarget{
	protected $id = self::POWERED_REPEATER_BLOCK;

	private static $updateQueue = [];

	public function __construct($meta = 0){
		$this->meta = $meta;

		if(count(self::$updateQueue) === 0){
			for($i = 2; $i <= 5; $i++){
				$queue = [
					[0, 1, 0], [0, -1, 0]
				];
				$base = Vector3::getSideRaw($i);
				$queue[] = $base;
				foreach([0, 1, 2, 3, 4, 5] as $face){
					if($face != Vector3::getOppositeSide($i)){
						$queue[] = Vector3::getSideRaw($face, $base);
					}
				}
				self::$updateQueue[$i] = $queue;
			}
		}
	}

	public function getUpdateQueue(){
		return self::$updateQueue[$this->getDirection()];
	}

	public function getLightLevel(){
		return$this->isPowered() ? 9 : 0;
	}

	public function isPowered() : bool {
		return $this->id == self::POWERED_REPEATER_BLOCK;
	}

	public function setPowered(bool $powered){
		$this->id = $powered ? self::POWERED_REPEATER_BLOCK : self::UNPOWERED_REPEATER_BLOCK;
		$this->getLevel()->setBlock($this, $this, false, true);
	}

	public function getName() : string{
		return "Powered Repeater";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getStrength(){
		return 15;
	}

	public function getDirection() : int{
		$faces = [
			0 => 3,
			1 => 4,
			2 => 2,
			3 => 5
		];
		return Vector3::getOppositeSide($faces[$this->meta % 4]);
	}

	public function getTickDelay() : int{
		return round(($this->meta - ($this->meta % 4)) / 4) + 1;
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$receiving = $this->isReceivingPower($this);
			if($this->isPowered() != $receiving){
				$this->getLevel()->setBlockCache($this, $receiving);
				$this->getLevel()->scheduleUpdate($this, $this->getTickDelay() * 2);
			}
		}
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->isLocked()){
				return;
			}
			$receiving = $this->isReceivingPower($this);
			if($this->getLevel()->getBlockCache($this) === true){
				if(!$this->isPowered()){
					$this->setPowered(true);
				}
				if(!$receiving){
					$this->getLevel()->scheduleUpdate($this, $this->getTickDelay() * 2);
				}
			}elseif($receiving != $this->isPowered()){
				$this->setPowered($receiving);
			}
		}
	}

	/**
	 * Checks whether a redstone repeater block is locked by one or more powered repeaters powering the sides
	 *
	 * @return bool True if locked, False if not
	 */
	public function isLocked() : bool{
		$faces = [];
		$face[1] = Vector3::getOppositeSide($face[0] = [2, 5, 3, 4][($this->getDirection() + 1) % 4]);
		foreach($faces as $face){
			$rel = $this->getSide($face);
			if($rel instanceof PoweredRepeater and $rel->hasDirectRedstonePower($rel, Vector3::getOppositeSide($face), self::POWER_MODE_ALL)){
				return true;
			}
		}
		return false;
	}

	public function onActivate(Item $item, Player $player = null){
		$meta = $this->meta + 4;
		if($meta > 15){
			$this->meta = $this->meta % 4;
		}else{
			$this->meta = $meta;
		}
		$this->getLevel()->setBlock($this, $this, true, false);
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = ((int) $player->getDirection() + 5) % 4;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
	}

	public function getDrops(Item $item) : array{
		return [
			[Item::REPEATER, 0, 1]
		];
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return 0;
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return $this->hasDirectRedstonePower($block, $face, $powerMode) ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->isPowered() and $this->getDirection() == $face;
	}

	public function isReceivingPower(Block $block) : bool{
		$face = $this->getDirection();
		return RedstoneUtil::isEmittingPower($block->getSide(Vector3::getOppositeSide($face)), $face);
	}
}
