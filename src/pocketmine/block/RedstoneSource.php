<?php
/**
 * Author: PeratX
 * Time: 2015/12/13 8:34
 ]

 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

/*
 * This class is the power of all redstone blocks!
 */

class RedstoneSource extends Flowable{
	protected $maxStrength = 15;
	protected $activated = false;

	public function __construct(){

	}

	public function getMaxStrength(){
		return $this->maxStrength;
	}

	public function isActivated(){
		return $this->activated;
	}

	public function canCalc(){
		return $this->getLevel()->getServer()->redstoneEnabled;
	}

	public function activateBlock(Block $block){
		if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
			if(!$block->isOpened()) $block->onActivate(new Item(0));
		}
		if($block->getId() == Block::TNT) $block->onActivate(new Item(Item::FLINT_AND_STEEL));
		/** @var InactiveRedstoneLamp $block*/
		if($block->getId() == Block::INACTIVE_REDSTONE_LAMP) $block->turnOn();
		if($block->getId() == Block::REDSTONE_WIRE){
			/** @var RedstoneWire $wire */
			$wire = $block;
			$wire->calcSignal($this->maxStrength, RedstoneWire::ON);
		}
		/** @var Dropper|Dispenser $block */
		if($block->getId() == Block::DROPPER or $block->getId() == Block::DISPENSER) $block->activate();
	}

	public function activate(array $ignore = []){
		if($this->canCalc()){
			$this->activated = true;
			/** @var Door $block */

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_DOWN];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					$this->activateBlock($block);
				}
			}
		}
	}

	public function deactivate(array $ignore = []){
		if($this->canCalc()){
			$this->activated = false;
			/** @var Door $block */

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!$this->checkPower($block)){
						if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
							if($block->isOpened()) $block->onActivate(new Item(0));
						}
						/** @var ActiveRedstoneLamp $block*/
						if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
					}
					if($block->getId() == Block::REDSTONE_WIRE){
						/** @var RedstoneWire $wire */
						$wire = $block;
						$wire->calcSignal(0, RedstoneWire::OFF);
					}
				}
			}

			if(!in_array(Vector3::SIDE_DOWN, $ignore)){
				$block = $this->getSide(Vector3::SIDE_DOWN);
				if(!$this->checkPower($block)){
					if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
				}

				$block = $this->getSide(Vector3::SIDE_DOWN, 2);
				if(!$this->checkPower($block)){
					if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
						if($block->isOpened()) $block->onActivate(new Item(0));
					}
					if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
				}
				if($block->getId() == Block::REDSTONE_WIRE){
					/** @var RedstoneWire $wire */
					$wire = $block;
					$wire->calcSignal(0, RedstoneWire::OFF);
				}
			}
		}
	}

	public function isRightPlace(Vector3 $p, Vector3 $pos){
		if($p->x == $pos->x and $p->y == $pos->y and $p->z == $pos->z) return true;
		return false;
	}

	public function checkPower(Block $block, array $ignore = [], $ignoreWire = false){
		$sides = [
			Vector3::SIDE_EAST,
			Vector3::SIDE_WEST,
			Vector3::SIDE_SOUTH,
			Vector3::SIDE_NORTH
		];
		foreach($sides as $side){
			if(!in_array($side, $ignore)){
				$pos = $block->getSide($side);
				if($pos instanceof RedstoneSource){
					if($pos->isActivated()){
						if(($ignoreWire and $pos->getId() != self::REDSTONE_WIRE) or (!$ignoreWire and $pos->getId() != self::REDSTONE_WIRE)) return true;
						if(!$ignoreWire and $pos->getId() == self::REDSTONE_WIRE){
							/** @var RedstoneWire $pos */
							$cb = $pos->getUnconnectedSide();
							if(!$cb[0]) return false;
							if($this->isRightPlace($this, $pos->getSide($cb[0]))) return true;
						}
					}
				}
			}
		}

		if($block->getId() == Block::ACTIVE_REDSTONE_LAMP and !in_array(Vector3::SIDE_UP, $ignore)){
			$pos = $block->getSide(Vector3::SIDE_UP);
			if($pos instanceof RedstoneSource and $pos->getId() != self::REDSTONE_TORCH){
				if($pos->isActivated()) return true;
			}
		}

		return false;
	}


	public function checkTorchOn(Block $pos, array $ignore = []){
		$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP];
		foreach($sides as $side){
			if(!in_array($side, $ignore)){
				/** @var RedstoneTorch $block */
				$block = $pos->getSide($side);
				if($block->getId() == self::REDSTONE_TORCH){
					$faces = [
							1 => 4,
							2 => 5,
							3 => 2,
							4 => 3,
							5 => 0,
							6 => 0,
							0 => 0,
					];
					if($this->isRightPlace($block->getSide($faces[$block->meta]), $pos)){
						$ignoreBlock = $this->getSide($this->getOppositeSide($faces[$block->meta]));
						$block->turnOff(Level::blockHash($ignoreBlock->x, $ignoreBlock->y, $ignoreBlock->z));
					}
				}
			}
		}
	}

	public function checkTorchOff(Block $pos, array $ignore = []){
		$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP];
		foreach($sides as $side){
			if(!in_array($side, $ignore)){
				/** @var RedstoneTorch $block */
				$block = $pos->getSide($side);
				if($block->getId() == self::UNLIT_REDSTONE_TORCH){
					$faces = [
							1 => 4,
							2 => 5,
							3 => 2,
							4 => 3,
							5 => 0,
							6 => 0,
							0 => 0,
					];
					if($this->isRightPlace($block->getSide($faces[$block->meta]), $pos)){
						$ignoreBlock = $this->getSide($this->getOppositeSide($faces[$block->meta]));
						$block->turnOn(Level::blockHash($ignoreBlock->x, $ignoreBlock->y, $ignoreBlock->z));
					}
				}
			}
		}
	}

	public function getStrength(){
		if($this->isActivated()) return $this->maxStrength;
		return 0;
	}
}