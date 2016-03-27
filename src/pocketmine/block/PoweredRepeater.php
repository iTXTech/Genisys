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
 * @link https://mcper.cn
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PoweredRepeater extends RedstoneSource{
	protected $id = self::POWERED_REPEATER;

	const ACTION_ACTIVATE = "Repeater Activate";
	const ACTION_DEACTIVATE = "Repeater Deactivate";

	public function __construct($meta = 0){
		$this->meta = $meta;
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
		return ($this->meta % 4);
	}

	public function getDelayLevel() : int{
		return round(($this->meta - ($this->getDirection())) / 4);
	}

	public function isActivated(Block $from = null){
		if(!$from instanceof Block){
			return false;
		}else{
			if($this->y != $from->y){
				return false;
			}
			if($this->equals($this->getSide($this->getDirection()))){
				return true;
			}
			return false;
		}
	}

	public function activate(array $ignore = []){
		if($this->canCalc()){
			if($this->id != self::POWERED_REPEATER){
				$this->id = self::POWERED_REPEATER;
				$this->getLevel()->setBlock($this, $this, true, false);
			}
			$this->getLevel()->setBlockTempData($this, self::ACTION_ACTIVATE);
			$this->getLevel()->scheduleUpdate($this, $this->getDelayLevel() * $this->getLevel()->getServer()->getTicksPerSecondAverage());
		}
	}

	public function deactivate(array $ignore = []){
		if($this->canCalc()){
			if($this->id != self::UNPOWERED_REPEATER){
				$this->id = self::UNPOWERED_REPEATER;
				$this->getLevel()->setBlock($this, $this, true, false);
			}
			$this->getLevel()->setBlockTempData($this, self::ACTION_DEACTIVATE);
			$this->getLevel()->scheduleUpdate($this, $this->getDelayLevel() * $this->getLevel()->getServer()->getTicksPerSecondAverage());
		}
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->getLevel()->getBlockTempData($this) == self::ACTION_ACTIVATE){
				$this->activateBlock($this->getSide($this->getDirection()));
				$this->activateBlock($this->getSide(Vector3::SIDE_DOWN, 2));
			}elseif($this->getLevel()->getBlockTempData($this) == self::ACTION_DEACTIVATE){
				$this->deactivateBlock($this->getSide($this->getDirection()));
				$this->deactivateBlock($this->getSide(Vector3::SIDE_DOWN, 2));//TODO: improve
			}
		}
		return $type;
	}

	public function onActivate(Item $item, Player $player = null){
		$meta = $this->meta + 4;
		if($meta > 15) $this->meta = $this->meta % 4;
		else $this->meta = $meta;
		$this->getLevel()->setBlock($this, $this, true, false);
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
			//TODO
		];
		$this->meta = $faces[$face];
		$this->getLevel()->setBlock($block, $this, true, false);
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, false);
		$this->deactivate();
		$this->getLevel()->setBlockTempData($this);
	}

	public function getDrops(Item $item) : array{
		return [
			[Item::REPEATER, 0, 1]
		];
	}
}