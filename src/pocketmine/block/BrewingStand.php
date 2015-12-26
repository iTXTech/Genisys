<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:20
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class BrewingStand extends Transparent {
	protected $id = self::BREWING_STAND;

	public function __construct() {
	}

	public function getName() {
		return "Brewing Stand";
	}

	public function getHardness() {
		return 0.5;
	}

	public function getToolType() {
		return Tool::TYPE_PICKAXE;
	}
}