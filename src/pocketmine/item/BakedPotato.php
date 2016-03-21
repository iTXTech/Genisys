<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 14:43
 ]

 */

namespace pocketmine\item;

class BakedPotato extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::BAKED_POTATO, $meta, $count, "Baked Potato");
	}

}