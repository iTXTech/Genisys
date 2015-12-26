<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:21
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

use pocketmine\item\Item;

class FlowerPot extends Transparent {
	protected $id = self::FLOWER_POT_BLOCK;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() {
		return "Flower Pot Block";
	}
}