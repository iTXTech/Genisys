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
 * @link https://mcper.cn
 *
 */

namespace pocketmine\item;

class SplashPotion extends Item{
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::SPLASH_POTION, $meta, $count, $this->getNameByMeta($meta));
	}

	public function getMaxStackSize() : int{
		return 1;
	}
	
	public function getNameByMeta(int $meta){
		switch($meta){
			case Potion::WATER_BOTTLE:
				return "Splash Water Bottle"; 
			case Potion::MUNDANE:
			case Potion::MUNDANE_EXTENDED:
				return "Splash Mundane Potion";
			case Potion::THICK:
				return "Splash Thick Potion";
			case Potion::AWKWARD:
				return "Splash Awkward Potion";
			case Potion::INVISIBILITY:
			case Potion::INVISIBILITY_T:
				return "Splash Potion of Invisibility";
			case Potion::LEAPING:
			case Potion::LEAPING_T:
				return "Splash Potion of Leaping";
			case Potion::LEAPING_TWO:
				return "Splash Potion of Leaping II";
			case Potion::FIRE_RESISTANCE:
			case Potion::FIRE_RESISTANCE_T:
				return "Splash Potion of Fire Residence";
			case Potion::SPEED:
			case Potion::SPEED_T:
				return "Splash Potion of Swiftness";
			case Potion::SPEED_TWO:
				return "Splash Potion of Swiftness II";
			case Potion::SLOWNESS:
			case Potion::SLOWNESS_T:
				return "Splash Potion of Slowness";
			case Potion::WATER_BREATHING:
			case Potion::WATER_BREATHING_T:
				return "Splash Potion of Water Breathing";
			case Potion::HARMING:
				return "Splash Potion of Harming";
			case Potion::HARMING_TWO:
				return "Splash Potion of Harming II";
			case Potion::POISON:
			case Potion::POISON_T:
				return "Splash Potion of Poison";
			case Potion::POISON_TWO:
				return "Splash Potion of Poison II";
			case Potion::HEALING:
				return "Splash Potion of Healing";
			case Potion::HEALING_TWO:
				return "Splash Potion of Healing II";
			case Potion::WEAKNESS:
			case Potion::WEAKNESS_T:
				return "Splash Potion of Weakness";
			case Potion::REGENERATION_TWO:
				return "Splash Potion of Regeneration II";
			case Potion::NIGHT_VISION:
			case Potion::NIGHT_VISION_T:
				return "Splash Potion of Night Vision";
			case Potion::STRENGTH:
			case Potion::STRENGTH_T:
				return "Splash Potion of Strength";
			case Potion::STRENGTH_TWO:
				return "Splash Potion of Strength II";
			case Potion::REGENERATION:
			case Potion::REGENERATION_T:
				return "Splash Potion of Regeneration";
			case Potion::REGENERATION_TWO:
				return "Splash Potion of Regeneration II";

			default:
				return "Splash Potion";
		}
	}

	
}