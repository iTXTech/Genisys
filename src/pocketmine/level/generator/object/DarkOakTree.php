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

namespace pocketmine\level\generator\object;

use pocketmine\block\Block;
use pocketmine\block\Leaves2;
use pocketmine\block\Wood2;

class DarkOakTree extends Tree{
	public function __construct(){
		$this->trunkBlock = Block::WOOD2;
		$this->leafBlock = Block::LEAVES2;
		$this->leafType = Leaves2::DARK_OAK;
		$this->type = Wood2::DARK_OAK;
		$this->treeHeight = 8;
	}
}