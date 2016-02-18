<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 20:22
 ]

 */
namespace pocketmine\item;

class ItemString extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::STRING, $meta, $count, "String");
	}

}