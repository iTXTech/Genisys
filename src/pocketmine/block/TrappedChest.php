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
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;

class TrappedChest extends Chest implements RedstoneSource{
	protected $id = self::TRAPPED_CHEST;

	public function getName() : string{
		return "Trapped Chest";
	}

	public function onActivate(Item $item, Player $player = null){
		$result = parent::onActivate($item, $player);
		$this->updateAround();
		return $result;
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return ($face == Vector3::SIDE_DOWN) ? $this->getRedstonePower($block) : 0;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return $this->getDirectRedstonePower($block, $face, $powerMode > 0);
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		$t = $this->getLevel()->getTile($this);
		if($t instanceof TileChest){
			$chest = $t;
		}else{
			$nbt = new CompoundTag("", [
				new ListTag("Items", []),
				new StringTag("id", Tile::CHEST),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			$chest = Tile::createTile(Tile::CHEST, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}
		$viewers = min(15, count($chest->getInventory()->getViewers()));
		return $viewers;
	}
}