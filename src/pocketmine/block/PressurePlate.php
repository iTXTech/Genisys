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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\sound\ClickSound;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;
use pocketmine\Player;

abstract class PressurePlate extends Flowable implements RedstoneSource{
	const TICK_DELAY = 20;

	public function __construct($meta = 0){
		$this->meta = $meta;

		if(self::$updateQueue == []){
			for($i = -1; $i <= 1; $i++){
				for($j = -1; $j <= 1; $j++){
					for($k = -1; $k <= 1; $k++){
						self::$updateQueue[] = [$i, $j, $k];
					}
				}
			}
			self::$updateQueue[] = [0, -2, 0];
			self::$updateQueue[] = [0, -1, 0];
		}
	}

	public function hasEntityCollision(){
		return true;
	}

	public function isPressed(){
		return $this->meta == 1;
	}

	public function onEntityCollide(Entity $entity){
		if($this->canTrigger($entity)){
			$this->setPressed(true);
		}
	}

	public abstract function canTrigger(Entity $entity) : bool;

	public function setPressed(bool $pressed){
		if($this->isPressed() != $pressed){
			$this->meta = $pressed ? 1 : 0;
			$this->getLevel()->setBlock($this, $this, false, false);
			$this->getLevel()->addSound(new ClickSound($this));
			$this->updateAround();
		}
		$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return $this->isPressed() ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return $this->hasDirectRedstonePower($block, $face, $powerMode) ? self::REDSTONE_POWER_MAX : self::REDSTONE_POWER_MIN;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->hasRedstonePower($block, $powerMode) and $face == self::SIDE_DOWN;
	}

	public function getIndirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return $this->getRedstonePower($block, $powerMode);
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0) instanceof Transparent){
				$this->getLevel()->useBreakOn($this);
			}
		}
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if(!$this->isPressed()){
				$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
				return;
			}

			$this->setPressed(false);
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$below = $this->getSide(Vector3::SIDE_DOWN);
		if($below instanceof Transparent){
			return false;
		}else {
			$this->getLevel()->setBlock($block, $this, true, false);
			$this->updateAround();
			$this->getLevel()->scheduleUpdate($this, self::TICK_DELAY);
			return true;
		}
	}

	public function getHardness() {
		return 0.5;
	}

	public function getResistance(){
		return 2.5;
	}

	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0 ,1]
		];
	}
}
