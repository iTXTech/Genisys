<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:22
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */

namespace pocketmine\block;

class IronTrapdoor extends Trapdoor {
	protected $id = self::IRON_TRAPDOOR;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() {
		return "Iron Trapdoor";
	}
}