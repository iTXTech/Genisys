<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/31 15:36
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\item;

use pocketmine\block\Block;

class ItemFrame extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = Block::get(Item::ITEM_FRAME_BLOCK);
		parent::__construct(self::ITEM_FRAME, 0, $count, "Item Frame");
	}
}