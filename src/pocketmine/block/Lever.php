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

class Lever extends Flowable implements RedstoneSource, Attachable{
	protected $id = self::LEVER;

	private static $updateQueue = [];

	public function __construct($meta = 0){
		$this->meta = $meta;

		if(count(self::$updateQueue) === 0){
			for($i = 0; $i <= 5; $i++){
				$sides = [0, 1, 2, 3, 4, 5];
				$queue = [];
				foreach($sides as $side){
					$queue[] = Vector3::getSideRaw($side);
				}
				$attachedSides = [0, 1, 2, 3, 4, 5];
				unset($attachedSides[Vector3::getOppositeSide($i)]);
				$base = Vector3::getSideRaw($i);
				foreach($attachedSides as $side){
					$queue[] = Vector3::getSideRaw($side, $base);
				}
				self::$updateQueue[$i] = $queue;
			}
		}
	}

	public function getUpdateQueue(){
		return self::$updateQueue[$this->getAttachedFace()];
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getName() : string{
		return "Lever";
	}

	public function onActivate(Item $item, Player $player = null){
		$this->toggle();
		return true;
	}

	public function getAttachedFace(){
		$faces = [
			5 => 0,
			6 => 0,
			3 => 2,
			1 => 4,
			4 => 3,
			2 => 5,
			0 => 1,
			7 => 1,
		];
		return $faces[$this->isToggled() ? $this->meta ^ 0x08 : $this->meta];
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$block = $this->getSide($this->getAttachedFace());
			if($block->isTransparent()){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$faces = [
				3 => 3,
				2 => 4,
				4 => 2,
				5 => 1,
			];
			if($face === 0){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 0 : 7);
			}elseif($face === 1){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 6 : 5);
			}else{
				$this->meta = $faces[$face];
			}
			$this->getLevel()->setBlock($block, $this, true, true);
			return true;
		}
		return false;
	}

	public function isToggled(){
		return (($this->meta & 0x08) === 0x08);
	}

	public function setToggled(bool $toggled){
		if($this->isToggled() != $toggled){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, true);
		}
	}

	public function toggle(){
		$toggled = !$this->isToggled();
		$this->setToggled($toggled);
		return $toggled;
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return $this->isToggled() ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return $this->hasDirectRedstonePower($block, $face, $powerMode) ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->hasRedstonePower($block, $powerMode) and $this->getAttachedFace() == $face;
	}

	public function getIndirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return $this->getRedstonePower($block, $powerMode);
	}

	public function getHardness(){
		return 0.5;
	}

	public function getResistance(){
		return 2.5;
	}

	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1],
		];
	}
}