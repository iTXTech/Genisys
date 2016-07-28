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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\item\enchantment\enchantment;

abstract class Tool extends Item{
	const TIER_WOODEN = 1;
	const TIER_GOLD = 2;
	const TIER_STONE = 3;
	const TIER_IRON = 4;
	const TIER_DIAMOND = 5;

	const TYPE_NONE = 0;
	const TYPE_SWORD = 1;
	const TYPE_SHOVEL = 2;
	const TYPE_PICKAXE = 3;
	const TYPE_AXE = 4;
	const TYPE_SHEARS = 5;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		parent::__construct($id, $meta, $count, $name);
	}

	public function getMaxStackSize() : int {
		return 1;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @param Entity|Block $object
	 * @param 1 for break|2 for Touch $type
	 *
	 * @return bool
	 */
	public function useOn($object, $type = 1){
		if($this->isUnbreakable()){
			return true;
		}

		$unbreakingl = $this->getEnchantmentLevel(Enchantment::TYPE_MINING_DURABILITY);
		$unbreakingl = $unbreakingl > 3 ? 3 : $unbreakingl;
		if (mt_rand(1, $unbreakingl + 1) !== 1) {
			return true;
		}

		if ($type === 1) {
			if ($object instanceof Entity) {
				if ($this->isHoe() !== false or $this->isSword() !== false) {
					//Hoe and Sword
					$this->meta++;
					return true;
				} elseif ($this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false) {
					//Pickaxe Axe and Shovel
					$this->meta += 2;
					return true;
				}
				return true;//Other tool do not lost durability white hitting
			} elseif ($object instanceof Block) {
				if ($this->isShears() !== false) {
					if ($object->getToolType() === Tool::TYPE_SHEARS) {//This should be checked in each block
						$this->meta++;
					}
					return true;
				} elseif ($object->getHardness() > 0) {//Sword Pickaxe Axe and Shovel
					if ($this->isSword() !== false) {
						$this->meta += 2;
						return true;
					} elseif ($this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false) {
						$this->meta += 1;
						return true;
					}
				}
			}
		} elseif ($type === 2) {//For Touch. only trigger when OnActivate return true
			if ($this->isHoe() !== false or $this->id === self::FLINT_STEEL or $this->isShovel() !== false) {
				$this->meta++;
				return true;
			}
		}
		return true;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @return int|bool
	 */
	public function getMaxDurability(){

		$levels = [
			Tool::TIER_GOLD => 33,
			Tool::TIER_WOODEN => 60,
			Tool::TIER_STONE => 132,
			Tool::TIER_IRON => 251,
			Tool::TIER_DIAMOND => 1562,
			self::FLINT_STEEL => 65,
			self::SHEARS => 239,
			self::BOW => 385,
		];

		if(($type = $this->isPickaxe()) === false){
			if(($type = $this->isAxe()) === false){
				if(($type = $this->isSword()) === false){
					if(($type = $this->isShovel()) === false){
						if(($type = $this->isHoe()) === false){
							$type = $this->id;
						}
					}
				}
			}
		}

		return $levels[$type];
	}

	public function isUnbreakable(){
		$tag = $this->getNamedTagEntry("Unbreakable");
		return $tag !== null and $tag->getValue() > 0;
	}

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return ($this->id === self::SHEARS);
	}

	public function isTool(){
		return ($this->id === self::FLINT_STEEL or $this->id === self::SHEARS or $this->id === self::BOW or $this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false or $this->isSword() !== false or $this->isHoe() !== false);
	}
}
