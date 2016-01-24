<?php

namespace pocketmine\block;

use pocketmine\level\Level;

class WallSign extends SignPost{
	
	protected $id = self::WALL_SIGN;
	
	public function getName() : string{
		return "Wall Sign";
	}
	
	public function onUpdate($type){
		$faces = [
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if(isset($faces[$this->meta])) {
				if ($this->getSide($faces[$this->meta])->getId() === self::AIR) {
					$this->getLevel()->useBreakOn($this);
				}
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
}