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
use pocketmine\item\Tool;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\tile\BrewingStand as TileBrewingStand;
use pocketmine\math\Vector3;

class BrewingStand extends Transparent{

	protected $id = self::BREWING_STAND_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($block->getSide(Vector3::SIDE_DOWN)->isTransparent() === false){
			$this->getLevel()->setBlock($block, $this, true, true);
			$nbt = new CompoundTag("", [
				new ListTag("Items", []),
				new StringTag("id", Tile::BREWING_STAND),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			if($item->hasCustomName()){
				$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
			}

			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $key => $v){
					$nbt->{$key} = $v;
				}
			}

			Tile::createTile(Tile::BREWING_STAND, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

			return true;
		}
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 0.5;
	}

	public function getResistance(){
		return 2.5;
	}

	public function getLightLevel(){
		return 1;
	}

	public function getName() : string{
		return "Brewing Stand";
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			//TODO lock
			if($player->isCreative() and $player->getServer()->limitedCreative){
				return true;
			}
			$t = $this->getLevel()->getTile($this);
			//$brewingStand = false;
			if($t instanceof TileBrewingStand){
				$brewingStand = $t;
			}else{
				$nbt = new CompoundTag("", [
					new ListTag("Items", []),
					new StringTag("id", Tile::BREWING_STAND),
					new IntTag("x", $this->x),
					new IntTag("y", $this->y),
					new IntTag("z", $this->z)
				]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$brewingStand = Tile::createTile(Tile::BREWING_STAND, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}
			$player->addWindow($brewingStand->getInventory());
		}
		return true;
	}

	public function getDrops(Item $item) : array {
		$drops = [];
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			$drops[] = [Item::BREWING_STAND, 0, 1];
		}
		return $drops;
	}
}
