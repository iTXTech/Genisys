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

use pocketmine\entity\Effect;

class Arrow extends Item {

	const REGENERATION_ARROW = 28;
	const REGENERATION_ARROW_T = 29;
	const REGENERATION_ARROW_TWO = 30;
	
	const SPEED_ARROW = 14;
	const SPEED_ARROW_T = 15;
	const SPEED_ARROW_TWO = 16;
	
	const FIRE_RESISTANCE_ARROW = 12;
	const FIRE_RESISTANCE_ARROW_T = 13;
	
	const HEALING_ARROW = 21;
	const HEALING_ARROW_TWO = 22;
	
	const NIGHT_VISION = 5;
	const NIGHT_VISION_T = 6;
	
	const STRENGTH_ARROW = 31;
	const STRENGTH_ARROW_T = 32;
	const STRENGTH_ARROW_TWO = 33;
	
	const LEAPING_ARROW = 9;
	const LEAPING_ARROW_T = 10;
	const LEAPING_ARROW_TWO = 11;
	
	const WATER_BREATHING_ARROW = 19;
	const WATER_BREATHING_ARROW_T = 20;
	
	const INVISIBILITY_ARROW = 7;
	const INVISIBILITY_ARROW_T = 8;
	
	const POISON_ARROW = 25;
	const POISON_ARROW_T = 26;
	const POISON_ARROW_TWO = 27;
	
	const WEAKNESS_ARROW = 34;
	const WEAKNESS_ARROW_T = 35;
	
	const SLOWNESS_ARROW = 17;
	const SLOWNESS_ARROW_T = 18;
	
	const HARMING_ARROW = 23;
	const HARMING_ARROW_TWO = 24;

	static $ARROW_LIST = [

		self::REGENERATION_ARROW => self::REGENERATION_ARROW,
		self::REGENERATION_ARROW_T => self::REGENERATION_ARROW_T,
		self::REGENERATION_ARROW_TWO => self::REGENERATION_ARROW_TWO,

		self::SPEED_ARROW => self::SPEED_ARROW,
		self::SPEED_ARROW_T => self::SPEED_ARROW_T,
		self::SPEED_ARROW_TWO => self::SPEED_ARROW_TWO,

		self::FIRE_RESISTANCE_ARROW => self::FIRE_RESISTANCE_ARROW,
		self::FIRE_RESISTANCE_ARROW_T => self::FIRE_RESISTANCE_ARROW_T,

		self::HEALING_ARROW => self::HEALING_ARROW,
		self::HEALING_ARROW_TWO => self::HEALING_ARROW_TWO,

		self::NIGHT_VISION => self::NIGHT_VISION,
		self::NIGHT_VISION_T => self::NIGHT_VISION_T,

		self::STRENGTH_ARROW => self::STRENGTH_ARROW,
		self::STRENGTH_ARROW_T => self::STRENGTH_ARROW_T,
		self::STRENGTH_ARROW_TWO => self::STRENGTH_ARROW_TWO,

		self::LEAPING_ARROW => self::LEAPING_ARROW,
		self::LEAPING_ARROW_T => self::LEAPING_ARROW_T,
		self::LEAPING_ARROW_TWO => self::LEAPING_ARROW_TWO,

		self::WATER_BREATHING_ARROW => self::WATER_BREATHING_ARROW,
		self::WATER_BREATHING_ARROW_T => self::WATER_BREATHING_ARROW_T,

		self::INVISIBILITY_ARROW => self::INVISIBILITY_ARROW,
		self::INVISIBILITY_ARROW_T => self::INVISIBILITY_ARROW_T,
		
		self::POISON_ARROW => self::POISON_ARROW,
		self::POISON_ARROW_T => self::POISON_ARROW_T,
		self::POISON_ARROW_TWO => self::POISON_ARROW_TWO,

		self::WEAKNESS_ARROW => self::WEAKNESS_ARROW,
		self::WEAKNESS_ARROW_T => self::WEAKNESS_ARROW_T,

		self::SLOWNESS_ARROW => self::SLOWNESS_ARROW,
		self::SLOWNESS_ARROW_T => self::SLOWNESS_ARROW_T,

		self::HARMING_ARROW => self::HARMING_ARROW,
		self::HARMING_ARROW_TWO => self::HARMING_ARROW_TWO,
	];
	/* TODO ADD luck arrow and effect.*/

	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::ARROW, $meta, $count, $this->getNameByMeta($meta));
	}

	public static function getColor(int $meta){
		return Effect::getEffect(self::getEffectId($meta))->getColor();
	}

	public static function getEffectId(int $meta) : int{
		switch($meta){
			case self::INVISIBILITY_ARROW:
			case self::INVISIBILITY_ARROW_T:
				return Effect::INVISIBILITY;
			case self::LEAPING_ARROW:
			case self::LEAPING_ARROW_T:
			case self::LEAPING_ARROW_TWO:
				return Effect::JUMP;
			case self::FIRE_RESISTANCE_ARROW:
			case self::FIRE_RESISTANCE_ARROW_T:
				return Effect::FIRE_RESISTANCE;
			case self::SPEED_ARROW:
			case self::SPEED_ARROW_T:
			case self::SPEED_ARROW_TWO:
				return Effect::SPEED;
			case self::SLOWNESS_ARROW:
			case self::SLOWNESS_ARROW_T:
				return Effect::SLOWNESS;
			case self::WATER_BREATHING_ARROW:
			case self::WATER_BREATHING_ARROW_T:
				return Effect::WATER_BREATHING;
			case self::HARMING_ARROW:
			case self::HARMING_ARROW_TWO:
				return Effect::HARMING;
			case self::POISON_ARROW:
			case self::POISON_ARROW_T:
			case self::POISON_ARROW_TWO:
				return Effect::POISON;
			case self::HEALING_ARROW:
			case self::HEALING_ARROW_TWO:
				return Effect::HEALING;
			case self::NIGHT_VISION_ARROW:
			case self::NIGHT_VISION_ARROW_T:
				return Effect::NIGHT_VISION;
			case self::REGENERATION_ARROW:
			case self::REGENERATION_ARROW_T:
			case self::REGENERATION_ARROW_TWO:
				return Effect::REGENERATION;
			default:
				return Effect::WATER_BREATHING;
		}
	}
	
	public function getNameByMeta(int $meta) : string{
		switch($meta){

			case self::INVISIBILITY_ARROW:
			case self::INVISIBILITY_ARROW_T:
				return "Arrow of Invisibility";
			case self::LEAPING_ARROW:
			case self::LEAPING_ARROW_T:
				return "Arrow of Leaping";
			case self::LEAPING_ARROW_TWO:
				return "Arrow of Leaping II";
			case self::FIRE_RESISTANCE_ARROW:
			case self::FIRE_RESISTANCE_ARROW_T:
				return "Arrow of Fire Resistance";
			case self::SPEED_ARROW:
			case self::SPEED_ARROW_T:
				return "Arrow of Speed";
			case self::SPEED_ARROW_TWO:
				return "Arrow of Speed II";
			case self::SLOWNESS_ARROW:
			case self::SLOWNESS_ARROW_T:
				return "Arrow of Slowness";
			case self::WATER_BREATHING_ARROW:
			case self::WATER_BREATHING_ARROW_T:
				return "Arrow of Water Breathing";
			case self::HARMING_ARROW:
				return "Arrow of Harming";
			case self::HARMING_ARROW_TWO:
				return "Arrow of Harming II";
			case self::POISON_ARROW:
			case self::POISON_ARROW_T:
				return "Arrow of Poison";
			case self::POISON_ARROW_TWO:
				return "Arrow of Poison II";
			case self::HEALING_ARROW:
				return "Arrow of Healing";
			case self::HEALING_ARROW_TWO:
				return "Arrow of Healing II";
			case self::NIGHT_VISION_ARROW:
			case self::NIGHT_VISION_ARROW_T:
				return "Arrow of Night Vision";
			case self::STRENGTH_ARROW:
			case self::STRENGTH_ARROW_T:
				return "Arrow of Strength";
			case self::STRENGTH_ARROW_TWO:
				return "Arrow of Strength II";
			case self::REGENERATION_ARROW:
			case self::REGENERATION_ARROW_T:
				return "Arrow of Regeneration";
			case self::REGENERATION_ARROW_TWO:
				return "Arrow of Regeneration II";
			case self::WEAKNESS_ARROW:
			case self::WEAKNESS_ARROW_T:
				return "Arrow of Weakness";
			default:
				return "Arrow";
		}
	}
	
}

