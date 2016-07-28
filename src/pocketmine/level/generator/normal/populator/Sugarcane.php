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

namespace pocketmine\level\generator\normal\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\normal\object\SugarCaneStack;
use pocketmine\level\generator\populator\Populator;
use pocketmine\utils\Random;

class SugarCane extends Populator{
	/** @var ChunkManager */
	private $level;
	private $randomAmount = 10;
	private $baseAmount = 1;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$this->level = $level;
		$canes = new SugarCaneStack($random);
		$successfulClusterCount = 0;
		for($count = 0; $count < $this->randomAmount; $count++){
			$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
			$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
			$y = $this->getHighestWorkableBlock($x, $z);
			if($y == -1 or !$canes->canPlaceObject($level, $x, $y, $z)){
				continue;
			}
			$successfulClusterCount++;
			$canes->randomize();
			$canes->placeObject($level, $x, $y, $z);
			for($placed = 1; $placed < 4; $placed++){
				$xx = $x - 3 + $random->nextBoundedInt(7);
				$zz = $z - 3 + $random->nextBoundedInt(7);
				$canes->randomize();
				if($canes->canPlaceObject($level, $xx, $y, $zz)){
					$canes->placeObject($level, $xx, $y, $zz);
				}
			}
			if($successfulClusterCount >= $this->baseAmount){
				return;
			}
		}
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 127; $y >= 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b !== Block::DIRT and $b !== Block::GRASS and $b !== Block::SAND){
				break;
			}
		}

		return $y === 0 ? -1 : ++$y;
	}
}
