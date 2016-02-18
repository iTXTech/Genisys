<?php
/**
 * Author: PeratX
 * Time: 2015/12/31 21:11
 ]

 *
 * OpenGenisys Project
 *
 * Merged from ImagicalMine
 */
namespace pocketmine\item;

use pocketmine\block\Block;

class Skull extends Item{
	const SKELETON = 0;
	const WITHER_SKELETON = 1;
	const ZOMBIE = 2;
	const STEVE = 3;
	const CREEPER = 4;

	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Block::SKULL_BLOCK);
		parent::__construct(self::SKULL, $meta, $count, "Skull");
	}

	public function getMaxStackSize() : int {
		return 64;
	}

}