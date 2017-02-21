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

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class Skull extends Spawnable{

	const TYPE_SKELETON = 0;
	const TYPE_WITHER = 1;
	const TYPE_ZOMBIE = 2;
	const TYPE_HUMAN = 3;
	const TYPE_CREEPER = 4;
	const TYPE_DRAGON = 5;

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new ByteTag("SkullType", 0);
		}
		if(!isset($nbt->Rot) or !($nbt->Rot instanceof ByteTag)) {
			$nbt->Rot = new ByteTag("Rot", 0);
		}
		parent::__construct($level, $nbt);
	}

	public function setType(int $type){
		if($type >= 0 && $type <= 4){
			$this->namedtag->SkullType = new ByteTag("SkullType", $type);
			$this->onChanged();
			return true;
		}
		return false;
	}

	public function getType() {
		return $this->namedtag["SkullType"];
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Creator);
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::SKULL),
			$this->namedtag->SkullType,
			$this->namedtag->Rot,
			new IntTag("x", (int)$this->x),
			new IntTag("y", (int)$this->y),
			new IntTag("z", (int)$this->z),
		]);
	}
}
