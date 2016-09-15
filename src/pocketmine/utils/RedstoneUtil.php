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

namespace pocketmine\utils;

use pocketmine\block\Block;
use pocketmine\block\IndirectRedstoneSource;
use pocketmine\block\RedstoneSource;
use pocketmine\math\Vector3;

class RedstoneUtil{
	public static function isConductor(Block $block) : bool{
		if($block instanceof IndirectRedstoneSource and $block->isRedstoneConductor()){
			return true;
		}
		return false;
	}

	public static function isReceivingPower(Block $block) : bool {
		return self::getReceivingPowerLocation($block) != null;
	}

	public static function getReceivingPowerLocation(Block $block){
		foreach([Vector3::SIDE_NORTH, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_DOWN, Vector3::SIDE_UP] as $face){
			$b = $block->getSide($face);
			if(self::isEmittingPower($b, Vector3::getOppositeSide($face))){
				return $b;
			}
		}
		return null;
	}

	public static function isEmittingPower(Block $block, int $face, int $powerMode = IndirectRedstoneSource::POWER_MODE_ALL) : bool{
		if(!($block instanceof IndirectRedstoneSource)){
			return false;
		}

		if($block instanceof RedstoneSource and $block->hasDirectRedstonePower($block, $face, $powerMode)){
			return true;
		}

		return $block->hasIndirectRedstonePower($block, $face, $powerMode);
	}
}