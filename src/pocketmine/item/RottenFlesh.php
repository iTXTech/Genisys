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

use pocketmine\entity\Effect;

class RottenFlesh extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::ROTTEN_FLESH, 0, $count, "Rotten Flesh");
	}
	
	public function getFoodRestore() : int{
		return 4;
	}

	public function getSaturationRestore() : float{
		return 0.8;
	}

	public function getAdditionalEffects() : array{
		$chance = mt_rand(0, 100);
		if($chance >= 20){
			return [Effect::getEffect(Effect::HUNGER)->setDuration(30 * 20)];
		}else{
			return [];
		}
	}
}