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
 
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class RedSandstoneSlab extends Slab{

	protected $id = Block::RED_SANDSTONE_SLAB;

	public function getName() : string{
		return "Red Sandstone Slab";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face === 0){
			if($target->getId() === self::RED_SANDSTONE_SLAB and ($target->getDamage() & 0x08) === 0x08){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_RED_SANDSTONE_SLAB, $this->meta), true);

				return true;
			}elseif($block->getId() === self::RED_SANDSTONE_SLAB){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_RED_SANDSTONE_SLAB, $this->meta), true);

				return true;
			}else{
				$this->meta |= 0x08;
			}
		}elseif($face === 1){
			if($target->getId() === self::RED_SANDSTONE_SLAB and ($target->getDamage() & 0x08) === 0){
				$this->getLevel()->setBlock($target, Block::get(Item::DOUBLE_RED_SANDSTONE_SLAB, $this->meta), true);

				return true;
			}elseif($block->getId() === self::RED_SANDSTONE_SLAB){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_RED_SANDSTONE_SLAB, $this->meta), true);

				return true;
			}
			//TODO: check for collision
		}else{
			if($block->getId() === self::RED_SANDSTONE_SLAB){
				$this->getLevel()->setBlock($block, Block::get(Item::DOUBLE_RED_SANDSTONE_SLAB, $this->meta), true);
			}else{
				if($fy > 0.5){
					$this->meta |= 0x08;
				}
			}
		}
		
		if($block->getId() === self::RED_SANDSTONE_SLAB and ($target->getDamage() & 0x07) !== ($this->meta & 0x07)){
			return false;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}
}