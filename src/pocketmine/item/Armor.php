<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/


namespace pocketmine\item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Color;
use pocketmine\item\enchantment\enchantment;

abstract class Armor extends Item{
	const TIER_LEATHER = 1;
	const TIER_GOLD = 2;
	const TIER_CHAIN = 3;
	const TIER_IRON = 4;
	const TIER_DIAMOND = 5;

	const TYPE_HELMET = 0;
	const TYPE_CHESTPLATE = 1;
	const TYPE_LEGGINGS = 2;
	const TYPE_BOOTS = 3;

	public function getMaxStackSize() : int {
		return 1;
	}

	public function isArmor(){
		return true;
	}

	/**
	 *
	 * @param Item $object
	 * @param int $cost
	 *
	 * @return bool
	 */
	public function useOn($object, int $cost = 1){
		if($this->isUnbreakable()){
			return true;
		}
		$unbreakings = [
			0 => 100,
			1 => 80,
			2 => 73,
			3 => 70
		];
		$unbreakingl = $this->getEnchantmentLevel(Enchantment::TYPE_MINING_DURABILITY);
		if(mt_rand(1, 100) > $unbreakings[$unbreakingl]){
			return true;
		}
		$this->setDamage($this->getDamage() + $cost);
		if($this->getDamage() >= $this->getMaxDurability()){
			$this->setCount(0);
		}
		return true;
	}

	public function isUnbreakable(){
		$tag = $this->getNamedTagEntry("Unbreakable");
		return $tag !== null and $tag->getValue() > 0;
	}

	public function setCustomColor(Color $color){
		if(($hasTag = $this->hasCompoundTag())){
			$tag = $this->getNamedTag();
		}else{
			$tag = new CompoundTag("", []);
		}
		$tag->customColor = new IntTag("customColor", $color->getColorCode());
		$this->setCompoundTag($tag);
	}

	public function getCustomColor(){
		if(!$this->hasCompoundTag()) return null;
		$tag = $this->getNamedTag();
		if(isset($tag->customColor)){
			return $tag["customColor"];
		}
		return null;
	}

	public function clearCustomColor(){
		if(!$this->hasCompoundTag()) return;
		$tag = $this->getNamedTag();
		if(isset($tag->customColor)){
			unset($tag->customColor);
		}
		$this->setCompoundTag($tag);
	}

	public function getArmorTier(){
		return false;
	}

	public function getArmorType(){
		return false;
	}

	public function getMaxDurability(){
		return false;
	}

	public function getArmorValue(){
		return false;
	}

	public function isHelmet(){
		return false;
	}

	public function isChestplate(){
		return false;
	}

	public function isLeggings(){
		return false;
	}

	public function isBoots(){
		return false;
	}
}