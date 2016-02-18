<?php
/**
 * Author: PeratX
 * Time: 2015/12/10 16:55
 ]

 */

namespace pocketmine\item;

class GlassBottle extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GLASS_BOTTLE, $meta, $count, "Glass Bottle");
	}
}