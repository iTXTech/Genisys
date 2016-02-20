<?php


namespace pocketmine\item;

use pocketmine\block\Block;

class Camera extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Item::CAMERA);
		parent::__construct(self::CAMERA, 0, $count, "Camera");
	}

	public function getMaxStackSize() : int {
		return 1;
	}
}
