<?php
namespace pocketmine\item;

use pocketmine\entity\Effect;

class Potion extends Item{
	
	const WATER_BOTTLE = 0;
	const AWKWARD = 4;
	const THICK = 3;
	const MUNDANE_EXTENDED = 2;
	const MUNDANE = 1;
	
	const REGENERATION = 28;
	const REGENERATION_T = 29;
	const REGENERATION_TWO = 30;
	
	const SPEED = 14;
	const SPEED_T = 15;
	const SPEED_TWO = 16;
	
	const FIRE_RESISTANCE = 12;
	const FIRE_RESISTANCE_T = 13;
	
	const HEALING = 21;
	const HEALING_TWO = 22;
	
	const NIGHT_VISION = 5;
	const NIGHT_VISION_T = 6;
	
	const STRENGTH = 31;
	const STRENGTH_T = 32;
	const STRENGTH_TWO = 33;
	
	const LEAPING = 9;
	const LEAPING_T = 10;
	const LEAPING_TWO = 11;
	
	const WATER_BREATHING = 19;
	const WATER_BREATHING_T = 20;
	
	const INVISIBILITY = 7;
	const INVISIBILITY_T = 8;
	
	const POISON = 25;
	const POISON_T = 26;
	const POISON_TWO = 27;
	
	const WEAKNESS = 34;
	const WEAKNESS_T = 35;
	
	const SLOWNESS = 17;
	const SLOWNESS_T = 18;
	
	const HARMING = 23;
	const HARMING_TWO = 24;
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::POTION, $meta, $count, $this->getNameByMeta($meta));
	}

	public static function getColor(int $meta){
		return Effect::getEffect(self::getEffectId($meta))->getColor();
	}

	public static function getEffectId(int $meta) : int{
		switch($meta){
			case self::INVISIBILITY:
			case self::INVISIBILITY_T:
				return Effect::INVISIBILITY;
			case self::LEAPING:
			case self::LEAPING_T:
			case self::LEAPING_TWO:
				return Effect::JUMP;
			case self::FIRE_RESISTANCE:
			case self::FIRE_RESISTANCE_T:
				return Effect::FIRE_RESISTANCE;
			case self::SPEED:
			case self::SPEED_T:
			case self::SPEED_TWO:
				return Effect::SPEED;
			case self::SLOWNESS:
			case self::SLOWNESS_T:
				return Effect::SLOWNESS;
			case self::WATER_BREATHING:
			case self::WATER_BREATHING_T:
				return Effect::WATER_BREATHING;
			case self::HARMING:
			case self::HARMING_TWO:
				return Effect::HARMING;
			case self::POISON:
			case self::POISON_T:
			case self::POISON_TWO:
				return Effect::POISON;
			case self::HEALING:
			case self::HEALING_TWO:
				return Effect::HEALING;
			case self::NIGHT_VISION:
			case self::NIGHT_VISION_T:
				return Effect::NIGHT_VISION;
			case self::REGENERATION:
			case self::REGENERATION_T:
			case self::REGENERATION_TWO:
				return Effect::REGENERATION;
			default:
				return Effect::WATER_BREATHING;
		}
	}
	
	public function getNameByMeta(int $meta) : string{
		switch($meta){
			case self::WATER_BOTTLE:
				return "Water Bottle"; 
			case self::MUNDANE:
			case self::MUNDANE_EXTENDED:
				return "Mundane Potion";
			case self::THICK:
				return "Thick Potion";
			case self::AWKWARD:
				return "Awkward Potion";
			case self::INVISIBILITY:
			case self::INVISIBILITY_T:
				return "Potion of Invisibility";
			case self::LEAPING:
			case self::LEAPING_T:
				return "Potion of Leaping";
			case self::LEAPING_TWO:
				return "Potion of Leaping II";
			case self::FIRE_RESISTANCE:
			case self::FIRE_RESISTANCE_T:
				return "Potion of Fire Residence";
			case self::SPEED:
			case self::SPEED_T:
				return "Potion of Speed";
			case self::SPEED_TWO:
				return "Potion of Speed II";
			case self::SLOWNESS:
			case self::SLOWNESS_T:
				return "Potion of Slowness";
			case self::WATER_BREATHING:
			case self::WATER_BREATHING_T:
				return "Potion of Water Breathing";
			case self::HARMING:
				return "Potion of Harming";
			case self::HARMING_TWO:
				return "Potion of Harming II";
			case self::POISON:
			case self::POISON_T:
				return "Potion of Poison";
			case self::POISON_TWO:
				return "Potion of Poison II";
			case self::HEALING:
				return "Potion of Healing";
			case self::HEALING_TWO:
				return "Potion of Healing II";
			case self::NIGHT_VISION:
			case self::NIGHT_VISION_T:
				return "Potion of Night Vision";
			default:
				return "Potion";
		}
	}
	
}