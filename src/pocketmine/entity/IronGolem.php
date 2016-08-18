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

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;

class IronGolem extends Animal{
	const NETWORK_ID = 20;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 2.8;
	
	public function initEntity(){
		$this->setMaxHealth(100);
		parent::initEntity();
	}
	
	public function getName() {
		return "Iron Golem";
	}

	public function getDrops(){
		//Not affected by Looting.
		$drops = array(ItemItem::get(ItemItem::IRON_INGOT, 0, mt_rand(3, 5)));
		$drops[] = ItemItem::get(ItemItem::POPPY, 0, mt_rand(0, 2));
		return $drops;
	}
}