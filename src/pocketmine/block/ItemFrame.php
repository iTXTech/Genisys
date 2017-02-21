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
use pocketmine\level\Level;
use pocketmine\nbt\tag\{
	ByteTag, CompoundTag, FloatTag, IntTag, StringTag
};
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use pocketmine\tile\Tile;

class ItemFrame extends Flowable{
	protected $id = Block::ITEM_FRAME_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Item Frame";
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if(!(($tile = $this->level->getTile($this)) instanceof TileItemFrame)){
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::ITEM_FRAME),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z),
				new FloatTag("ItemDropChance", 1.0),
				new ByteTag("ItemRotation", 0)
			]);
			$tile = Tile::createTile(Tile::ITEM_FRAME, $this->level->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}

		if($tile->hasItem()){
			$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
		}else{
			if($item->getCount() > 0){
				$frameItem = clone $item;
				$frameItem->setCount(1);
				$item->setCount($item->getCount() - 1);
				$tile->setItem($frameItem);
				if($player instanceof Player and $player->isSurvival()){
					$player->getInventory()->setItemInHand($item->getCount() <= 0 ? Item::get(Item::AIR) : $item);
				}
			}
		}

		return true;
	}

	public function onBreak(Item $item){
		if(($tile = $this->level->getTile($this)) instanceof TileItemFrame){
			//TODO: add events
			if(lcg_value() <= $tile->getItemDropChance() and $tile->getItem()->getId() !== Item::AIR){
				$this->level->dropItem($tile->getBlock(), $tile->getItem());
			}
		}
		return parent::onBreak($item);
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$sides = [
				0 => 4,
				1 => 5,
				2 => 2,
				3 => 3
			];
			if(!$this->getSide($sides[$this->meta])->isSolid()){
				$this->level->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face === 0 or $face === 1){
			return false;
		}

		$faces = [
			2 => 3,
			3 => 2,
			4 => 1,
			5 => 0
		];

		$this->meta = $faces[$face];
		$this->level->setBlock($block, $this, true, true);

		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::ITEM_FRAME),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new FloatTag("ItemDropChance", 1.0),
			new ByteTag("ItemRotation", 0)
		]);

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::ITEM_FRAME, $this->level->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		return true;

	}

	public function getDrops(Item $item) : array{
		return [
			[Item::ITEM_FRAME, 0, 1]
		];
	}

}