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
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;

class Cow extends Animal{
	const NETWORK_ID = self::COW;

	public $width = 1.4;
	public $length = 1.4;
	public $height = 1.4;

	public $dropExp = [1, 3];
	
	public function getName() : string{
		return "Cow";
	}
	
	public function useItemOn(ItemItem $item, Player $player): bool{
		switch($item->getId()){
			case ItemItem::BUCKET:
				if($player->isCreative()){
					//Cannot milk cows in creative. This seems dumb, but it holds true in PE and PC vanilla both.
					return false;
				}
				if($item->getDamage() !== 0){ //Need an empty bucket
					return false;
				}
				$inv = $player->getInventory();
				$new = $inv->getItemInHand();
				$new->setDamage(1);
				$inv->setItemInHand($new);
				//Don't need to send updates to the origin player, that will be handled by the client automatically.
				break;
			default:
				return parent::useItemOn($item, $player);
		}
		return true;
	}
	
	public function getDrops(){
		$lootingLevel = 0;
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player){
			$lootingLevel = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
		}
		$drops = array(ItemItem::get(ItemItem::RAW_BEEF, 0, mt_rand(1, 3 + $lootingLevel)));
		$drops[] = ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2 + $lootingLevel));
		//TODO: add judgement for Steak
		/*if ($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player) {
			$drops[] = ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0,2));
		}*/
		return $drops;
	}
}