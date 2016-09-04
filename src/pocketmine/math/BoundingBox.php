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

namespace pocketmine\math;

class BoundingBox extends AxisAlignedBB{
	public function getXSize(){
		return $this->maxX - $this->minX;
	}

	public function getYSize(){
		return $this->maxY - $this->minY;
	}

	public function getZSize(){
		return $this->maxZ - $this->minZ;
	}

	public function intersectsWith(BoundingBox $bb){
		return parent::intersectsWith($bb);
	}

	public function equals(BoundingBox $bb){
		if($this->maxX == $bb->maxX and $this->maxY == $bb->maxY and $this->maxZ == $this->minZ
			and $this->minX == $bb->minX and $this->minY == $bb->minY and $this->minZ == $bb->minZ){
			return true;
		}
		return false;
	}
}