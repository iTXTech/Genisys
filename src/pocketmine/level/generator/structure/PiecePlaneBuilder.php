<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\level\generator\structure;

class PiecePlaneBuilder extends PieceCuboidBuilder{
	public function __construct(StructurePiece $parent){
		parent::__construct($parent);
	}

	protected function isOuter(int $xx, int $yy, int $zz){
		if ($this->min->getX() == $this->max->getX()) {
			return $yy == $this->min->getY() || $zz == $this->min->getZ() || $yy == $this->max->getY() || $zz == $this->max->getZ();
		} else if ($this->min->getY() == $this->max->getY()) {
			return $xx == $this->min->getX() || $zz == $this->min->getZ() || $xx == $this->max->getX() || $zz == $this->max->getZ();
		} else if ($this->min->getZ() == $this->max->getZ()) {
			return $yy == $this->min->getY() || $xx == $this->min->getX() || $yy == $this->max->getY() || $xx == $this->max->getX();
		} else {
			return parent::isOuter($xx, $yy, $zz);
		}
	}
}