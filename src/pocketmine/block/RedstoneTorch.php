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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\math\Vector3;

class RedstoneTorch extends RedstoneSource{

	protected $id = self::REDSTONE_TORCH;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getLightLevel(){
		return 7;
	}

	public function getName(){
		return "Redstone Torch";
	}

	public function turnOn($ignore = []){
		return true;
	}

	public function turnOff($ignore = []){
		$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
		];
		$this->getLevel()->setBlock($this, new UnlitRedstoneTorch($this->meta), true);
		return $this->deactivateTorch([$faces[$this->meta]], $ignore);
	}

	public function activateTorch(array $ignore = [], $notCheck = []){
		if($this->canCalc()){
			$this->activated = true;
			/** @var Door $block */

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
						if(($block instanceof Door) or ($block instanceof Trapdoor)){
							if(!$block->isOpened()) $block->onActivate(new Item(0));
						}
						if($block->getId() == Block::TNT) $block->onActivate(new Item(Item::FLINT_AND_STEEL));
						/** @var InactiveRedstoneLamp $block */
						if($block->getId() == Block::INACTIVE_REDSTONE_LAMP) $block->turnOn();
						if($block->getId() == Block::REDSTONE_WIRE){
							/** @var RedstoneWire $wire */
							$wire = $block;
							$notCheck = $wire->calcSignal($this->maxStrength, RedstoneWire::ON, [], [], $notCheck)[2];
						}
					}
				}
			}

			if(!in_array(Vector3::SIDE_DOWN, $ignore)){
				$block = $this->getSide(Vector3::SIDE_DOWN);
				if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
					if($block->getId() == Block::INACTIVE_REDSTONE_LAMP) $block->turnOn();

					$block = $this->getSide(Vector3::SIDE_DOWN, 2);
					if(($block instanceof Door) or ($block instanceof Trapdoor)){
						if(!$block->isOpened()) $block->onActivate(new Item(0));
					}
					if($block->getId() == Block::TNT) $block->onActivate(new Item(Item::FLINT_AND_STEEL));
					if($block->getId() == Block::INACTIVE_REDSTONE_LAMP or $block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOn();
					if($block->getId() == Block::REDSTONE_WIRE){
						/** @var RedstoneWire $wire */
						$wire = $block;
						$notCheck = $wire->calcSignal($this->maxStrength, RedstoneWire::ON, [], [], $notCheck)[2];
					}
				}
			}
		}
		return $notCheck;
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
						if(!$this->checkPower($block)){
							if(($block instanceof Door) or ($block instanceof Trapdoor)){
								if($block->isOpened()) $block->onActivate(new Item(0));
							}
							/** @var ActiveRedstoneLamp $block */
							if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
						}
						if($block->getId() == Block::REDSTONE_WIRE){
							/** @var RedstoneWire $wire */
							$wire = $block;
							$notCheck = $wire->calcSignal($this->maxStrength, RedstoneWire::OFF, [], [], $notCheck)[2];
						}
					}
				}
			}

			if(!in_array(Vector3::SIDE_DOWN, $ignore)){
				$block = $this->getSide(Vector3::SIDE_DOWN);
				if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
					if(!$this->checkPower($block)){
						if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
					}

					$block = $this->getSide(Vector3::SIDE_DOWN, 2);
					if(!$this->checkPower($block)){
						if(($block instanceof Door) or ($block instanceof Trapdoor)){
							if($block->isOpened()) $block->onActivate(new Item(0));
						}
						if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
					}
					if($block->getId() == Block::REDSTONE_WIRE){
						/** @var RedstoneWire $wire */
						$wire = $block;
						$notCheck = $wire->calcSignal($this->maxStrength, RedstoneWire::OFF, [], [], $notCheck)[2];
					}
				}
			}
		}
		return $notCheck;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$below = $this->getSide(0);
			$side = $this->getDamage();
			$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
			];

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

	public function getDrops(Item $item){
		return [
			[$this->id, 0, 1],
		];
	}

	public function isActivated(){
		return true;
	}
}