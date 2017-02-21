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

namespace pocketmine\block;

class InactiveRedstoneLamp extends ActiveRedstoneLamp{
	protected $id = self::INACTIVE_REDSTONE_LAMP;

	public function getLightLevel(){
		return 0;
	}

	public function getName() : string{
		return "Inactive Redstone Lamp";
	}

	public function isLightedByAround(){
		return false;
	}

	public function turnOn(){
		//if($isLightedByAround){
		$this->getLevel()->setBlock($this, new ActiveRedstoneLamp(), true, true);
		/*}else{
			$this->getLevel()->setBlock($this, new ActiveRedstoneLamp(), true, false);
			//$this->lightAround();
		}*/
		return true;
	}

	public function turnOff(){
		return true;
	}
}
