<?php
/**
 * Author: PeratX
 * Time: 2015/12/6 20:22
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\item;

class String extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::STRING, $meta, $count, "String");
	}

}