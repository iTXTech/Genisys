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

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\enchantment\enchantment;

class Gravel extends Fallable{

	protected $id = self::GRAVEL;

	public function __construct(){

	}

	public function getName() : string{
		return "Gravel";
	}

	public function getHardness() {
		return 0.6;
	}

	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	public function getDrops(Item $item) : array {
		$drops = [];
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){//使用精准采集附魔 不掉落燧石
			$drops[] = [Item::GRAVEL, 0, 1];
			return $drops;
		}
		$fortunel = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
		$fortunel = $fortunel > 3 ? 3 : $fortunel;
		$rates = [10,7,4,1];
		if(mt_rand(1, $rates[$fortunel]) === 1){//10% 14% 25% 100%
			$drops[] = [Item::FLINT, 0, 1];
		}
		if(mt_rand(1, 10) !== 1){//90%
			$drops[] = [Item::GRAVEL, 0, 1];
		}
		return $drops;
	}
}
