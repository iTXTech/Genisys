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

namespace pocketmine\item;


class LeatherPants extends Armor{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::LEATHER_PANTS, $meta, $count, "Leather Pants");
	}

	public function getArmorTier(){
		return Armor::TIER_LEATHER;
	}

	public function getArmorType(){
		return Armor::TYPE_LEGGINGS;
	}

	public function getMaxDurability(){
		return 76;
	}

	public function getArmorValue(){
		return 2;
	}

	public function isLeggings(){
		return true;
	}
}