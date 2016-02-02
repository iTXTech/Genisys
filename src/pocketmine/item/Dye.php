<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\item;

class Dye extends Item{
	const BLACK = 0;
	const RED = 1;
	const GREEN = 2;
	const BROWN = 3;
	const BLUE = 4;
	const PURPLE = 5;
	const CYAN = 6;
	const SILVER = 7;
	const GRAY = 8;
	const PINK = 9;
	const LIME = 10;
	const YELLOW = 11;
	const LIGHT_BLUE = 12;
	const MAGENTA = 13;
	const ORANGE = 14;
	const WHITE = 15;

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::DYE, $meta, $count, "Dye");
	}

}

