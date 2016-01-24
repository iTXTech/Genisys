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
use pocketmine\math\Vector3;

class Obsidian extends Solid{

	protected $id = self::OBSIDIAN;

	public function __construct(){

	}

	public function getName() : string{
		return "Obsidian";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getHardness() {
		return 50;
	}

	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= 5){
			return [
				[Item::OBSIDIAN, 0, 1],
			];
		}else{
			return [];
		}
	}
	
	public function onBreak(Item $item) {
		parent::onBreak($item);
		
		if($this->getLevel()->getServer()->netherEnabled){
			for($i = 0;$i <= 6;$i++){
				if($i == 6){
					return;
				}elseif($this->getLevel()->getBlock($this->getSide($i))->getId() == 90){
					$side = $i;
					break;
				}
			}
			$block = $this->getLevel()->getBlock($this->getSide($i));
			if($this->getLevel()->getBlock($block->add(-1, 0, 0))->getId() == 90 or $this->getLevel()->getBlock($block->add(1, 0, 0))->getId() == 90){//x方向
				for($x = $block->getX();$this->getLevel()->getBlock(new Vector3($x, $block->getY(), $block->getZ()))->getId() == 90;$x++){
					for($y = $block->getY();$this->getLevel()->getBlock(new Vector3($x, $y, $block->getZ()))->getId() == 90;$y++){
						$this->getLevel()->setBlock(new Vector3($x, $y, $block->getZ()), new Block(0, 0));
					}
					for($y = $block->getY() - 1;$this->getLevel()->getBlock(new Vector3($x, $y, $block->getZ()))->getId() == 90;$y--){
						$this->getLevel()->setBlock(new Vector3($x, $y, $block->getZ()), new Block(0, 0));
					}
				}
				for($x = $block->getX() - 1;$this->getLevel()->getBlock(new Vector3($x, $block->getY(), $block->getZ()))->getId() == 90;$x--){
					for($y = $block->getY();$this->getLevel()->getBlock(new Vector3($x, $y, $block->getZ()))->getId() == 90;$y++){
						$this->getLevel()->setBlock(new Vector3($x, $y, $block->getZ()), new Block(0, 0));
					}
					for($y = $block->getY() - 1;$this->getLevel()->getBlock(new Vector3($x, $y, $block->getZ()))->getId() == 90;$y--){
						$this->getLevel()->setBlock(new Vector3($x, $y, $block->getZ()), new Block(0, 0));
					}
				}
			}else{//z方向
				for($z = $block->getZ();$this->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $z))->getId() == 90;$z++){
					for($y = $block->getY();$this->getLevel()->getBlock(new Vector3($block->getX(), $y, $z))->getId() == 90;$y++){
						$this->getLevel()->setBlock(new Vector3($block->getX(), $y, $z), new Block(0, 0));
					}
					for($y = $block->getY() - 1;$this->getLevel()->getBlock(new Vector3($block->getX(), $y, $z))->getId() == 90;$y--){
						$this->getLevel()->setBlock(new Vector3($block->getX(), $y, $z), new Block(0, 0));
					}
				}
				for($z = $block->getZ() - 1;$this->getLevel()->getBlock(new Vector3($block->getX(), $block->getY(), $z))->getId() == 90;$z--){
					for($y = $block->getY();$this->getLevel()->getBlock(new Vector3($block->getX(), $y, $z))->getId() == 90;$y++){
						$this->getLevel()->setBlock(new Vector3($block->getX(), $y, $z), new Block(0, 0));
					}
					for($y = $block->getY() - 1;$this->getLevel()->getBlock(new Vector3($block->getX(), $y, $z))->getId() == 90;$y--){
						$this->getLevel()->setBlock(new Vector3($block->getX(), $y, $z), new Block(0, 0));
					}
				}
			}
		}
	}
}