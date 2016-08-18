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

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class Chicken extends Animal{
	const NETWORK_ID = self::CHICKEN;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

	public $dropExp = [1, 3];
	
	public function getName() : string{
		return "Chicken";
	}
	
	public function getDrops(){
		$drops = [];
		if ($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player) {
			
				switch (\mt_rand(0, 2)) {
					case 0:
						$drops[] = ItemItem::get(ItemItem::RAW_CHICKEN, 0, 1);
						break;
					case 1:
						$drops[] = ItemItem::get(ItemItem::FEATHER, 0, 1);
						break;
					case 2:
						$drops[] = ItemItem::get(ItemItem::FEATHER, 0, 2);
						break;
				}
		}
		return $drops;
	}
}