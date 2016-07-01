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

namespace pocketmine\item\enchantment;

use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\utils\Range;

class EnchantmentLevelTable{

	private static $map = [];

	public static function init(){
		self::$map = [
			Enchantment::TYPE_ARMOR_PROTECTION => [
				new Range(1, 21),
				new Range(12, 32),
				new Range(23, 43),
				new Range(34, 54)
			],

			Enchantment::TYPE_ARMOR_FIRE_PROTECTION => [
				new Range(10, 22),
				new Range(18, 30),
				new Range(26, 38),
				new Range(34, 46)],

			Enchantment::TYPE_ARMOR_FALL_PROTECTION => [
				new Range(5, 12),
				new Range(11, 21),
				new Range(17, 27),
				new Range(23, 33)
			],

			Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION => [
				new Range(5, 17),
				new Range(13, 25),
				new Range(21, 33),
				new Range(29, 41)
			],

			Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION => [
				new Range(3, 18),
				new Range(9, 24),
				new Range(15, 30),
				new Range(21, 36)
			],

			Enchantment::TYPE_WATER_BREATHING => [
				new Range(10, 40),
				new Range(20, 50),
				new Range(30, 60)
			],

			Enchantment::TYPE_WATER_AFFINITY => [
				new Range(10, 41)
			],

			Enchantment::TYPE_ARMOR_THORNS => [
				new Range(10, 60),
				new Range(30, 80),
				new Range(50, 100)
			],

			//Weapon
			Enchantment::TYPE_WEAPON_SHARPNESS => [
				new Range(1, 21),
				new Range(12, 32),
				new Range(23, 43),
				new Range(34, 54),
				new Range(45, 65)
			],

			Enchantment::TYPE_WEAPON_SMITE => [
				new Range(5, 25),
				new Range(13, 33),
				new Range(21, 41),
				new Range(29, 49),
				new Range(37, 57)
			],

			Enchantment::TYPE_WEAPON_ARTHROPODS => [
				new Range(5, 25),
				new Range(13, 33),
				new Range(21, 41),
				new Range(29, 49),
				new Range(37, 57)
			],

			Enchantment::TYPE_WEAPON_KNOCKBACK => [
				new Range(5, 55),
				new Range(25, 75)
			],

			Enchantment::TYPE_WEAPON_FIRE_ASPECT => [
				new Range(10, 60),
				new Range(30, 80)
			],

			Enchantment::TYPE_WEAPON_LOOTING => [
				new Range(15, 65),
				new Range(24, 74),
				new Range(33, 83)
			],

			//Bow
			Enchantment::TYPE_BOW_POWER => [
				new Range(1, 16),
				new Range(11, 26),
				new Range(21, 36),
				new Range(31, 46),
				new Range(41, 56)
			],

			Enchantment::TYPE_BOW_KNOCKBACK => [
				new Range(12, 37),
				new Range(32, 57)
			],

			Enchantment::TYPE_BOW_FLAME => [
				new Range(20, 50)
			],

			Enchantment::TYPE_BOW_INFINITY => [
				new Range(20, 50)
			],

			//Mining
			Enchantment::TYPE_MINING_EFFICIENCY => [
				new Range(1, 51),
				new Range(11, 61),
				new Range(21, 71),
				new Range(31, 81),
				new Range(41, 91)
			],

			Enchantment::TYPE_MINING_SILK_TOUCH => [
				new Range(15, 65)
			],

			Enchantment::TYPE_MINING_DURABILITY => [
				new Range(5, 55),
				new Range(13, 63),
				new Range(21, 71)
			],

			Enchantment::TYPE_MINING_FORTUNE => [
				new Range(15, 55),
				new Range(24, 74),
				new Range(33, 83)
			],

			//Fishing
			Enchantment::TYPE_FISHING_FORTUNE => [
				new Range(15, 65),
				new Range(24, 74),
				new Range(33, 83)
			],

			Enchantment::TYPE_FISHING_LURE => [
				new Range(15, 65),
				new Range(24, 74),
				new Range(33, 83)
			]
		];
	}

	/**
	 * @param Item $item
	 * @param int  $modifiedLevel
	 * @return Enchantment[]
	 */
	public static function getPossibleEnchantments(Item $item, int $modifiedLevel){
		$result = [];

		$enchantmentIds = [];

		if($item->getId() == Item::BOOK){
			$enchantmentIds = array_keys(self::$map);
		}elseif($item->isArmor()){
			$enchantmentIds[] = Enchantment::TYPE_ARMOR_PROTECTION; 
			$enchantmentIds[] = Enchantment::TYPE_ARMOR_FIRE_PROTECTION; 
			$enchantmentIds[] = Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION; 
			$enchantmentIds[] = Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION; 
			$enchantmentIds[] = Enchantment::TYPE_ARMOR_THORNS; 

			if($item->isBoots()){
				$enchantmentIds[] = Enchantment::TYPE_ARMOR_FALL_PROTECTION; 
			}

			if($item->isHelmet()){
				$enchantmentIds[] = Enchantment::TYPE_WATER_BREATHING; 
				$enchantmentIds[] = Enchantment::TYPE_WATER_AFFINITY; 
			}

		}elseif($item->isSword()){
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_SHARPNESS; 
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_SMITE; 
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_ARTHROPODS; 
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_KNOCKBACK; 
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_FIRE_ASPECT; 
			$enchantmentIds[] = Enchantment::TYPE_WEAPON_LOOTING; 

		}elseif($item->isTool()){
			$enchantmentIds[] = Enchantment::TYPE_MINING_EFFICIENCY; 
			$enchantmentIds[] = Enchantment::TYPE_MINING_SILK_TOUCH; 
			$enchantmentIds[] = Enchantment::TYPE_MINING_FORTUNE; 

		}elseif($item->getId() == Item::BOW){
			$enchantmentIds[] = Enchantment::TYPE_BOW_POWER; 
			$enchantmentIds[] = Enchantment::TYPE_BOW_KNOCKBACK; 
			$enchantmentIds[] = Enchantment::TYPE_BOW_FLAME; 
			$enchantmentIds[] = Enchantment::TYPE_BOW_INFINITY; 

		}elseif($item->getId() == Item::FISHING_ROD){
			$enchantmentIds[] = Enchantment::TYPE_FISHING_FORTUNE; 
			$enchantmentIds[] = Enchantment::TYPE_FISHING_LURE; 

		}

		if($item->isTool() || $item->isArmor()){
			$enchantmentIds[] = Enchantment::TYPE_MINING_DURABILITY; 
		}

		foreach($enchantmentIds as $enchantmentId) {
			$enchantment = Enchantment::getEnchantment($enchantmentId);
            $ranges = self::$map[$enchantmentId];
            $i = 0;
			/** @var Range $range */
			foreach($ranges as $range) {
	            $i++;
	            if($range->isInRange($modifiedLevel)){
		            $result[] = $enchantment->setLevel($i);
	            }
            }
        }

        return $result;
    }

}
