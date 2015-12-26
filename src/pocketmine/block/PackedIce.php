<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:26
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class PackedIce extends Solid {

	protected $id = self::PACKED_ICE;

	public function __construct() {

	}

	public function getName() {
		return "Packed Ice";
	}

	public function getHardness() {
		return 0.5;
	}

	public function getToolType() {
		return Tool::TYPE_PICKAXE;
	}

} 
