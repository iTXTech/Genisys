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

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\utils\RedstoneUtil;

class ActiveRedstoneLamp extends Solid implements SolidLight, RedstoneTarget{
	protected $id = self::ACTIVE_REDSTONE_LAMP;

	private static $updateQueue = [];

	public function getUpdateQueue(){
		return self::$updateQueue;
	}

	public function __construct(){
		if(count(self::$updateQueue) === 0){
			for($i = -2; $i <= 2; $i++){
				for($j = -2; $j <= 2; $j++){
					for($k = -2; $k <= 2; $k++){
						self::$updateQueue[] = [$i, $j, $k];
					}
				}
			}
		}
	}

	public function getName() : string{
		return "Active Redstone Lamp";
	}

	public function getHardness() {
		return 0.3;
	}

	public function getResistance(){
		return 1.5;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getLightLevel(){
		return $this->isOn() ? IndirectRedstoneSource::REDSTONE_POWER_MAX : IndirectRedstoneSource::REDSTONE_POWER_MIN;
	}

	public function getDrops(Item $item) : array {
		return [
			[Item::INACTIVE_REDSTONE_LAMP, 0 ,1],
		];
	}

	public function isOn() : bool{
		return ($this->id == self::ACTIVE_REDSTONE_LAMP);
	}

	public function isReceivingPower() : bool{
		return RedstoneUtil::isReceivingPower($this);
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			$power = $this->isReceivingPower();
			if($this->isOn() != $power){
				if($power){
					$this->id = self::ACTIVE_REDSTONE_LAMP;
				}else{
					$this->id = self::INACTIVE_REDSTONE_LAMP;
				}
				$this->getLevel()->setBlock($this, $this, false, true);
			}
		}
	}
}