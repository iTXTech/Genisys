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
use pocketmine\level\sound\ButtonClickSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class WoodenButton extends Flowable implements RedstoneSource, Attachable{
	const TICK_DELAY = 20;

	protected $id = self::WOODEN_BUTTON;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getAttachedFace(){
		$faces = [
			0 => 1,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];
		return $faces[$this->isPressed() ? $this->meta ^ 0x08 : $this->meta];
	}

	public function getUpdateQueue(){
		$sides = [0, 1, 2, 3, 4, 5];
		$queue = [];
		foreach($sides as $side){
			$queue[] = Vector3::getSideRaw($side);
		}
		$attachedSides = [0, 1, 2, 3, 4, 5];
		unset($attachedSides[Vector3::getOppositeSide($this->getAttachedFace())]);
		$base = Vector3::getSideRaw($this->getAttachedFace());
		foreach($attachedSides as $side){
			$queue[] = Vector3::getSideRaw($side, $base);
		}
		return $queue;
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			$this->setPressed(false);
			return Level::BLOCK_UPDATE_SCHEDULED;
		}
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide($this->getAttachedFace()) instanceof Transparent){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
			if($this->isPressed()){
				$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
			}
		}
		return false;
	}

	public function getName() : string{
		return "Wooden Button";
	}

	public function getHardness() {
		return 0.5;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$this->meta = $face;
			$this->getLevel()->setBlock($block, $this, true, true);
			return true;
		}
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function setPressed(bool $pressed){
		if($this->isPressed() != $pressed){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, true);
			$this->getLevel()->addSound(new ButtonClickSound($this));
		}
	}

	public function isPressed(){
		return (($this->meta & 0x08) === 0x08);
	}

	public function onActivate(Item $item, Player $player = null){
		$this->setPressed(true);
		return true;
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return $this->isPressed() ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
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
}
