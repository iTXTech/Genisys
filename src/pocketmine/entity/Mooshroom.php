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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\Player;

class Mooshroom extends Animal{
	const NETWORK_ID = self::MOOSHROOM;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;
	
	public function getName() : string{
		return "Mooshroom";
	}
	
	public function useItemOn(ItemItem $item): bool{
		switch($item->getId()){
			case ItemItem::SHEARS:
				$this->shear();
				break;
			default: return parent::useItemOn($item);
		}
		return true;
	}

	public function shear(){
		if(!$this->isBaby()){
			$this->level->dropItem($this, ItemItem::get(ItemItem::RED_MUSHROOM, 0, 5));
			$normalCow = Entity::createEntity(Entity::COW, $this->chunk, $this->namedtag); //Create a cow in the exact same position with the same data
			$normalCow->spawnToAll();
			$this->close();
		}
	}

	public function getDrops(){
		$lootingLevel = 0;
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player){
			$lootingLevel = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
		}
		$drops = array(ItemItem::get(ItemItem::RAW_BEEF, 0, mt_rand(1, 3 + $lootingLevel)));
		$drops[] = ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2 + $lootingLevel));
		return $drops;
	}
}
