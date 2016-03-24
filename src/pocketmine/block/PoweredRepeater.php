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
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://mcper.cn
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class PoweredRepeater extends RedstoneSource{
	protected $id = self::POWERED_REPEATER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function isActivated(Block $from = null){
		return true;
	}

	public function activate(array $ignore = []){
	}

	public function deactivate(array $ignore = []){
	}

	public function onActivate(Item $item, Player $player = null){
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
	}

	public function onBreak(Item $item){
	}
}