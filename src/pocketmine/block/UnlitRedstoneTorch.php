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
		return 0;
	}

	public function isActivated(){
		return false;
	}
}