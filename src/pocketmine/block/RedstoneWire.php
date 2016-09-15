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
use pocketmine\Server;
use pocketmine\utils\RedstoneUtil;

class RedstoneWire extends Flowable implements RedstoneSource, RedstoneTarget{
	protected $id = self::REDSTONE_WIRE;

	/** @var Block[] */
	private $powerUpdateQueue = [];

	public function __construct($meta = 0){
		parent::__construct(Block::REDSTONE_WIRE, $meta);

		if(self::$updateQueue == []){
			for($i = -1; $i <= 1; $i++){
				for($j = -1; $j <= 1; $j++){
					for($k = -1; $k <= 1; $k++){
						self::$updateQueue[] = [$i, $j, $k];
					}
				}
			}
		}
	}

	public function getName() : string{
		return "Redstone Wire";
	}

	public function getHardness(){
		return 0;
	}

	public function getResistance(){
		return 0;
	}

	public function getDrops(Item $item) : array{
		return [
			[Item::REDSTONE_DUST, 0, 1]
		];
	}

	public function disableRedstone(Block $middle){
		foreach([Vector3::SIDE_NORTH, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_DOWN, Vector3::SIDE_UP] as $face){
			$block = $middle->getSide($face);
			if($block->getId() == $this->id){
				if($block->getDamage() > 0){
					$block->meta = 0;
					$this->getLevel()->setBlock($block, $block, false, false);
					$this->powerUpdateQueue[] = $block;
					$this->disableRedstone($block);
				}
			}
		}
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL or $type == Level::BLOCK_UPDATE_SCHEDULED){
			if($type == Level::BLOCK_UPDATE_NORMAL){
				$b = $this->getSide(0);
				if($b instanceof Transparent){
					$this->getLevel()->useBreakOn($this);
					return;
				}
			}
			if(!Server::getInstance()->redstoneEnabled){
				return;
			}

			$receiving = $this->getReceivingPower($this);
			$current = $this->getRedstonePower($this);
			if($current == $receiving){
				if($type == Level::BLOCK_UPDATE_SCHEDULED){
					$this->updateAround();
				}
			}elseif($receiving > $current){
				$this->meta = $receiving;
				$this->getLevel()->setBlock($this, $this, false, false);
				$this->updateAround();
			}else{
				$this->powerUpdateQueue = [];
				$this->disableRedstone($this);
				foreach([Vector3::SIDE_NORTH, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_DOWN] as $face){
					$this->disableRedstone($this->getSide($face));
				}
				foreach($this->powerUpdateQueue as $block){
					$block->onUpdate(Level::BLOCK_UPDATE_SCHEDULED);
				}
				$this->powerUpdateQueue = [];
			}
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($this->getSide(0) instanceof Transparent){
			return false;
		}
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	public function getIndirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return 0;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->getDirectRedstonePower($block, $face, $powerMode) > 0;
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		if($powerMode == self::POWER_MODE_ALL_EXCEPT_WIRE){
			return self::REDSTONE_POWER_MIN;
		}

		$power = $this->getRedstonePower($block);
		if($power == self::REDSTONE_POWER_MIN){
			return $power;
		}

		$mat = $block->getSide($face);
		if($mat instanceof RedstoneSource or !$this->isDistractedFrom($block, $face)){
			return $power;
		}

		return self::REDSTONE_POWER_MIN;
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		return ($powerMode == self::POWER_MODE_ALL_EXCEPT_WIRE) ? self::REDSTONE_POWER_MIN : $block->getDamage();
	}

	public function isReceivingPower(Block $block) : bool{
		return $this->getReceivingPower($block) > 0;
	}

	public function getReceivingPower(Block $block){
		$maxPower = 0;
		$topIsConductor = false;
		foreach([Vector3::SIDE_DOWN, Vector3::SIDE_UP, Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH] as $face){
			$rel = $block->getSide($face);
			if($rel->getId() == $this->id){
				$maxPower = max($maxPower, $this->getRedstonePower($rel) - 1);
			}else{
				$maxPower = max($maxPower, $rel->getRedstonePower($rel, self::POWER_MODE_ALL_EXCEPT_WIRE));
				if($rel instanceof RedstoneSource){
					$maxPower = max($maxPower, $rel->getDirectRedstonePower($rel, Vector3::getOppositeSide($face), self::POWER_MODE_ALL));
				}
			}
			if($maxPower == self::REDSTONE_POWER_MAX){
				return $maxPower;
			}

			if($face == Vector3::SIDE_UP){
				$topIsConductor = RedstoneUtil::isConductor($rel);
			}elseif($face != Vector3::SIDE_DOWN){
				if(!RedstoneUtil::isConductor($rel)){
					$relvert = $rel->getSide(Vector3::SIDE_DOWN);
					if($relvert->getId() == $this->id){
						$maxPower = max($maxPower, $this->getRedstonePower($relvert) - 1);
					}
				}
				if(!$topIsConductor){
					$relvert = $rel->getSide(Vector3::SIDE_UP);
					if($relvert->getId() == $this->id){
						$maxPower = max($maxPower, $this->getRedstonePower($relvert) - 1);
					}
				}
			}
		}
		return $maxPower;
	}

	public function isConnectedToSource(Block $block, int $face){
		$target = $block->getSide($face);
		if($target instanceof RedstoneSource){
			return true;
		}

		if(!RedstoneUtil::isConductor($target)){
			if($target->getSide(Vector3::SIDE_DOWN) instanceof RedstoneSource){
				return true;
			}
		}

		if($target->getSide(Vector3::SIDE_UP)->getId() == $this->id){
			if(!RedstoneUtil::isConductor($block->getSide(Vector3::SIDE_UP))){
				return true;
			}
		}
		return false;
	}

	public function isDistractedFrom(Block $block, int $face){
		switch($face){
			case Vector3::SIDE_NORTH:
			case Vector3::SIDE_SOUTH:
				return $this->isConnectedToSource($block, Vector3::SIDE_EAST) or $this->isConnectedToSource($block, Vector3::SIDE_WEST);
			case Vector3::SIDE_EAST:
			case Vector3::SIDE_WEST:
				return $this->isConnectedToSource($block, Vector3::SIDE_NORTH) or $this->isConnectedToSource($block, Vector3::SIDE_SOUTH);
			default:
				return false;
		}
	}
}