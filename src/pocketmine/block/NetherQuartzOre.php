<?php
/**
 * Author: PeratX
 * Time: 2016/1/1 10:42
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class NetherQuartzOre extends Solid{
	protected $id = self::NETHER_QUARTZ_ORE;

	public function __construct(){

	}

	public function getName(){
		return "Nether Quartz Ore";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getDrops(Item $item){
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			return [
				[Item::NETHER_QUARTZ, 0, 1],
			];
		}else{
			return [];
		}
	}
}