<?php
/**
 * Author: PeratX
 * Time: 2015/12/29 23:54
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
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
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;

class Skull extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->SkullType)){
			$nbt->SkullType = new String("SkullType", 0);
		}

		parent::__construct($chunk, $nbt);
	}

	public function saveNBT(){
		parent::saveNBT();
		unset($this->namedtag->Creator);
	}

	public function getSpawnCompound(){
		return new Compound("", [
			new String("id", Tile::SKULL),
			$this->namedtag->SkullType,
			new Int("x", (int)$this->x),
			new Int("y", (int)$this->y),
			new Int("z", (int)$this->z),
			$this->namedtag->Rot
		]);
	}

	public function getSkullType(){
		return $this->namedtag["SkullType"];
	}
}
