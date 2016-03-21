<?php
/**
 * Author: PeratX
 * Time: 2015/12/29 23:54
 ]

 *
 * OpenGenisys Project
 */
/*
 * Copied from ImagicalMine.
 * THIS IS COPIED FROM THE PLUGIN FlowerPot MADE BY @beito123!!
 * https://github.com/beito123/PocketMine-MP-Plugins/blob/master/test%2FFlowerPot%2Fsrc%2Fbeito%2FFlowerPot%2Fomake%2FSkull.php
 *
 */

namespace pocketmine\tile;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class Skull extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new StringTag("SkullType", 0);
		}

		parent::__construct($chunk, $nbt);
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Creator);
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::SKULL),
			$this->namedtag->SkullType,
			new IntTag("x", (int)$this->x),
			new IntTag("y", (int)$this->y),
			new IntTag("z", (int)$this->z),
			$this->namedtag->Rot
		]);
	}

	public function getSkullType(){
		return $this->namedtag["SkullType"];
	}
}
