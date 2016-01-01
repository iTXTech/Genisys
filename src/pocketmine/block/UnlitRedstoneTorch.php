<?php
/**
 * Author: PeratX
 * Time: 2015/12/22 21:12
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

class UnlitRedstoneTorch extends RedstoneTorch{
	protected $id = self::UNLIT_REDSTONE_TORCH;

	public function getLightLevel(){
		return 15;
	}

	public function isActivated(){
		return false;
	}

	public function turnOff($ignore = []){
		return true;
	}

	public function turnOn($ignore = []){
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		$this->getLevel()->setBlock($this, new RedstoneTorch($this->meta), true);
		/** @var RedstoneTorch $block */
		$block = $this->getLevel()->getBlock($this);
		return $block->activateTorch([$faces[$this->meta]], $ignore);
	}
}