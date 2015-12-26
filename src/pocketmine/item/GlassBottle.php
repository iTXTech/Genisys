<?php
/**
 * Author: PeratX
 * Time: 2015/12/10 16:55
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */

namespace pocketmine\item;

class GlassBottle extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GLASS_BOTTLE, $meta, $count, "Glass Bottle");
	}
}