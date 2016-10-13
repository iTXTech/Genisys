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
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\level\sound\DoorSound;
use pocketmine\utils\RedstoneUtil;

class FenceGate extends Transparent implements RedstoneTarget{

	protected $id = self::FENCE_GATE;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Oak Fence Gate";
	}

	public function getHardness() {
		return 2;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}


	protected function recalculateBoundingBox() {

		if(($this->getDamage() & 0x04) > 0){
			return null;
		}

		$i = ($this->getDamage() & 0x03);
		if($i === 2 or $i === 0){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + 0.375,
				$this->x + 1,
				$this->y + 1.5,
				$this->z + 0.625
			);
		}else{
			return new AxisAlignedBB(
				$this->x + 0.375,
				$this->y,
				$this->z,
				$this->x + 0.625,
				$this->y + 1.5,
				$this->z + 1
			);
		}
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$powered = $this->isReceivingPower();
			if($this->isOpen() != $powered){
				$this->setOpen($powered);
			}
		}
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
			0 => 3,
			1 => 0,
			2 => 1,
			3 => 2,
		];
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0] & 0x03;
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}

	public function isOpen() : bool{
		return (($this->getDamage() & 0x04) > 0);
	}

	public function setOpen(bool $open){
		if($this->isOpen() != $open){
			$this->meta ^= 0x04;
			$this->getLevel()->setBlock($this, $this, false, false);
			$this->level->addSound(new DoorSound($this));
		}
	}

	public function toogleOpen(){
		$this->setOpen(!$this->isOpen());
	}

	public function isReceivingPower() : bool{
		return RedstoneUtil::isReceivingPower($this);
	}

	public function getDrops(Item $item) : array {
		return [
			[$this->id, 0, 1],
		];
	}

	public function onActivate(Item $item, Player $player = null){
		$faces = [
			0 => 3,
			1 => 0,
			2 => 1,
			3 => 2,
		];
		if($player !== null){
			$this->meta = ($faces[$player instanceof Player ? $player->getDirection() : 0] & 0x03) | ((~$this->meta) & 0x04);
		}else{
			$this->meta ^= 0x04;
		}
		$this->getLevel()->setBlock($this, $this, true, false);
		$this->level->addSound(new DoorSound($this));
		return true;
	}
}
