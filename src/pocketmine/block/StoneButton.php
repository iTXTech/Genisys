<?php
/**
 * Author: PeratX
 * Time: 2015/12/20 20:14
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

class StoneButton extends WoodenButton{
	protected $id = self::STONE_BUTTON;

	public function getName(){
		return "Stone Button";
	}
}