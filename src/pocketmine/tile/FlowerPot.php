<?php
/**
 * Author: PeratX
 * Time: 2015/12/29 23:52
 ]

 *
 * OpenGenisys Project
 */
/*
 * Copied from ImagicalMine
 * THIS IS COPIED FROM THE PLUGIN FlowerPot MADE BY @beito123!!
 * https://github.com/beito123/PocketMine-MP-Plugins/blob/master/test%2FFlowerPot%2Fsrc%2Fbeito%2FFlowerPot%2Fomake%2FSkull.php
 *
 */

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;

class FlowerPot extends Spawnable{

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->item)){
			$nbt->item = new ShortTag("item", 0);
		}
		if(!isset($nbt->data)){
			$nbt->data = new IntTag("data", 0);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getFlowerPotItem(){
		return $this->namedtag["item"];
	}

	public function getFlowerPotData(){
		return $this->namedtag["data"];
	}

	public function setFlowerPotData($item, $data){
		$this->namedtag->item = new ShortTag("item", (int) $item);
		$this->namedtag->data = new IntTag("data", (int) $data);
		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
			/*$block = $this->level->getBlock($this);
			if($block->getId() === Block::FLOWER_POT_BLOCK){
				$this->level->setBlock($this, Block::get(Block::FLOWER_POT_BLOCK, $data), true, true);
			}*/
		}
		return true;
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::FLOWER_POT),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ShortTag("item", (int) $this->namedtag["item"]),
			new IntTag("data", (int) $this->namedtag["data"])
		]);
	}
}