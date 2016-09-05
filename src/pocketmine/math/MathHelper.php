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

namespace pocketmine\math;

use pocketmine\utils\Random;

class MathHelper{
	public static function chooseWeightedRandom(Random $random, array $weightMap){
		$totalWeight = 0;//[Content, Weight]
		foreach($weightMap as $i){
			$totalWeight += $i[1];
		}
		if($totalWeight <= 0){
			return null;
		}
		$j = $random->nextRange(0, $totalWeight);
		foreach($weightMap as $w){
			$j -= $w[1];
			if($j < 0){
				return $w[0];
			}
		}
		return null;
	}
}