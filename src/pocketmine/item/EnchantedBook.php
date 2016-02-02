<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/2 11:23
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\item;

class EnchantedBook extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::ENCHANTED_BOOK, $meta, $count, "Enchanted Book");
	}
}