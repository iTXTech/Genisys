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
 * @link https://itxtech.org
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\enchantment\enchantment;

class BrownMushroomBlock extends Solid{
	
	const BROWN = 14;

	protected $id = self::BROWN_MUSHROOM_BLOCK;

	public function __construct($meta = 14){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getName() : string{
		return "Brown Mushroom Block";
	}

	public function getHardness() {
		return 0.2;
	}

	public function getResistance(){
		return 1;
	}
	
	public function getDrops(Item $item) : array {
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::BROWN_MUSHROOM_BLOCK, SELF::BROWN, 1],
			];
		}else{
			return [
				[Item::BROWN_MUSHROOM, 0, mt_rand(0, 2)],
			];
		}
	}
}
