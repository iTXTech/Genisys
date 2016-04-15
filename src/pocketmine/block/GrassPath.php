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


use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;


class GrassPath extends Transparent{

	protected $id = self::GRASS_PATH;

	public function __construct(){

	}

	public function getName() : string{
		return "Grass Path";
	}

	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	protected function recalculateBoundingBox() {
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.9375,
			$this->z + 1
		);
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$block = $this->getSide(self::SIDE_UP);
			if($block->getId() != self::AIR){
				$this->getLevel()->setBlock($this, new Dirt(), true);
			}
			return Level::BLOCK_UPDATE_NORMAL;
		}
		return false;
	}

	public function getHardness() {
		return 0.6;
	}

	public function getDrops(Item $item) : array {
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::GRASS_PATH, 0, 1],
			];
		}else{
			return [
				[Item::DIRT, 0, 1],
			];
		}
	}
}
