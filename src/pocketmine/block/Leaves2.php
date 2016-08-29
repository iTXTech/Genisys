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

namespace pocketmine\block;

use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\item\Item;
use pocketmine\item\enchantment\enchantment;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class Leaves2 extends Leaves{

	const WOOD_TYPE = self::WOOD2;

	protected $id = self::LEAVES2;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		static $names = [
			self::ACACIA => "Acacia Leaves",
			self::DARK_OAK => "Dark Oak Leaves",
		];
		return $names[$this->meta & 0x01];
	}

	public function getDrops(Item $item) : array {
		$drops = [];
		if($item->isShears() or $item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			$drops[] = [$this->id, $this->meta & 0x01, 1];
		}else{
			$fortunel = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
			$fortunel = min(3, $fortunel);
			$rates = [20,16,12,10];
			if(mt_rand(1, $rates[$fortunel]) === 1){ //Saplings
				$drops[] = [Item::SAPLING, ($this->meta & 0x01) | 0x04, 1];
			}
		}

		return $drops;
	}
}
