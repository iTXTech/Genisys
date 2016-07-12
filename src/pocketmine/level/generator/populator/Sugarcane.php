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

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class Sugarcane extends Populator{
	/** @var ChunkManager */
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
			$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);

			$y = $this->getHighestWorkableBlock($x, $z);
			$tallRand = $random->nextRange(0, 17);
			$yMax = $y + 2 + (int) ($tallRand > 10) + (int) ($tallRand > 15);
			if($y !== -1){
				for(; $y < 127 and $y < $yMax; $y++){
					if($this->canSugarcaneStay($x, $y, $z)){
						$this->level->setBlockIdAt($x, $y, $z, Block::SUGARCANE_BLOCK);
						$this->level->setBlockDataAt($x, $y, $z, 1);
					}
				}
			}
		}
	}

	private function canSugarcaneStay($x, $y, $z){
		$b = $this->level->getBlockIdAt($x, $y, $z);
		$below = $this->level->getBlockIdAt($x, $y - 1, $z);
		$water = false;
		foreach(array($this->level->getBlockIdAt($x + 1, $y - 1, $z), $this->level->getBlockIdAt($x - 1, $y - 1, $z), $this->level->getBlockIdAt($x, $y - 1, $z + 1), $this->level->getBlockIdAt($x, $y - 1, $z - 1)) as $adjacent){
			if($adjacent === Block::WATER or $adjacent === Block::STILL_WATER){
				$water = true;
				break;
			}
		}
		return ($b === Block::AIR) and ((($below === Block::SAND or $below === Block::GRASS) and $water) or ($below === Block::SUGARCANE_BLOCK));
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 127; $y >= 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b !== Block::AIR and $b !== Block::LEAVES and $b !== Block::LEAVES2){
				break;
			}
		}

		return $y === 0 ? -1 : ++$y;
	}
}
