<?php
/**
 * Author: PeratX
 * Time: 2015/12/24 17:06
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;

class DaylightDetectorInverted extends DaylightDetector{
	protected $id = self::DAYLIGHT_SENSOR_INVERTED;

	public function onActivate(Item $item, Player $player = null){
		$this->getLevel()->setBlock($this, new DaylightDetector(), true, true);
		return true;
	}

	public function isActivated(){
		if(!$this->hasStartedUpdate) $this->onUpdate(Level::BLOCK_UPDATE_NORMAL);
		return ($this->getLightByTime() == 0);
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL or $type == Level::BLOCK_UPDATE_SCHEDULED){
			$this->hasStartedUpdate = true;
			if($this->getLightByTime() == 0) $this->activate();
			else $this->deactivate();
			$this->getLevel()->scheduleUpdate($this, $this->getLevel()->getServer()->getTicksPerSecondAverage() * 3);
			return Level::BLOCK_UPDATE_NORMAL;
		}
		return true;
	}
}