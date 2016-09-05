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

namespace pocketmine\level\generator\nether\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\VariableAmountPopulator;
use pocketmine\utils\Random;

class NetherLava extends VariableAmountPopulator{
	/** @var ChunkManager */
	private $level;

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		if(mt_rand(0, 100) < 5){
			$this->level = $level;
			$amount = $this->getAmount($random);
			for($i = 0; $i < $amount; ++$i){
				$x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
				$z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
				$y = $this->getHighestWorkableBlock($x, $z);
				if($y !== -1 and $this->canNetherLavaStay($x, $y, $z)){
					$this->level->setBlockIdAt($x, $y, $z, Block::LAVA);
					$this->level->updateBlockLight($x, $y, $z);
					$this->lavaSpread($x, $y, $z);
				}
			}
		}
	}

	private function getFlowDecay($x1, $y1, $z1, $x2, $y2, $z2){
		if($this->level->getBlockIdAt($x1, $y1, $z1) !== $this->level->getBlockIdAt($x2, $y2, $z2)){
			return -1;
		}else{
			return $this->level->getBlockDataAt($x2, $y2, $z2);
		}
	}

	private function lavaSpread($x, $y, $z){
		if($this->level->getChunk($x >> 4, $z >> 4) == null){
			return;
		}
		$decay = $this->getFlowDecay($x, $y, $z, $x, $y, $z);
		$multiplier = 2;

		if($decay > 0){
			$smallestFlowDecay = -100;
			$smallestFlowDecay = $this->getSmallestFlowDecay($x, $y, $z, $x, $y, $z - 1, $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($x, $y, $z, $x, $y, $z + 1, $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($x, $y, $z, $x - 1, $y, $z, $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($x, $y, $z, $x + 1, $y, $z, $smallestFlowDecay);

			$k = $smallestFlowDecay + $multiplier;

			if($k >= 8 or $smallestFlowDecay < 0){
				$k = -1;
			}

			if(($topFlowDecay = $this->getFlowDecay($x, $y, $z, $x, $y + 1, $z)) >= 0){
				if($topFlowDecay >= 8){
					$k = $topFlowDecay;
				}else{
					$k = $topFlowDecay | 0x08;
				}
			}

			if($decay < 8 and $k < 8 and $k > 1 and mt_rand(0, 4) !== 0){
				$k = $decay;
			}

			if($k !== $decay){
				$decay = $k;
				if($decay < 0){
					$this->level->setBlockIdAt($x, $y, $z, 0);
				}else{
					$this->level->setBlockIdAt($x, $y, $z, Block::LAVA);
					$this->level->setBlockDataAt($x, $y, $z, $decay);
					$this->level->updateBlockLight($x, $y, $z);
					$this->lavaSpread($x, $y, $z);
					return;
				}
			}
		}

		if($this->canFlowInto($x, $y - 1, $z)){
			if($decay >= 8){
				$this->flowIntoBlock($x, $y - 1, $z, $decay);
			}else{
				$this->flowIntoBlock($x, $y - 1, $z, $decay | 0x08);
			}
		}elseif($decay >= 0 and ($decay === 0 or !$this->canFlowInto($x, $y - 1, $z))){
			$flags = $this->getOptimalFlowDirections($x, $y, $z);

			$l = $decay + $multiplier;

			if($decay >= 8){
				$l = 1;
			}

			if($l >= 8){
				return;
			}

			if($flags[0]){
				$this->flowIntoBlock($x - 1, $y, $z, $l);
			}

			if($flags[1]){
				$this->flowIntoBlock($x + 1, $y, $z, $l);
			}

			if($flags[2]){
				$this->flowIntoBlock($x, $y, $z - 1, $l);
			}

			if($flags[3]){
				$this->flowIntoBlock($x, $y, $z + 1, $l);
			}
		}
	}

	private function flowIntoBlock($x, $y, $z, $newFlowDecay){
		if($this->level->getBlockIdAt($x, $y, $z) === Block::AIR){
			$this->level->setBlockIdAt($x, $y, $z, Block::LAVA);
			$this->level->setBlockDataAt($x, $y, $z, $newFlowDecay);
			$this->level->updateBlockLight($x, $y, $z);
			$this->lavaSpread($x, $y, $z);
		}
	}

	private function canFlowInto($x, $y, $z){
		$id = $this->level->getBlockIdAt($x, $y, $z);
		if($id === Block::AIR or $id === Block::LAVA or $id === Block::STILL_LAVA){
			return true;
		}
		return false;
	}

	private function calculateFlowCost($xx, $yy, $zz, $accumulatedCost, $previousDirection){
		$cost = 1000;

		for($j = 0; $j < 4; ++$j){
			if(
				($j === 0 and $previousDirection === 1) or
				($j === 1 and $previousDirection === 0) or
				($j === 2 and $previousDirection === 3) or
				($j === 3 and $previousDirection === 2)
			){
				$x = $xx;
				$y = $yy;
				$z = $zz;

				if($j === 0){
					--$x;
				}elseif($j === 1){
					++$x;
				}elseif($j === 2){
					--$z;
				}elseif($j === 3){
					++$z;
				}

				if(!$this->canFlowInto($x, $y, $z)){
					continue;
				}elseif($this->canFlowInto($x, $y, $z) and $this->level->getBlockDataAt($x, $y, $z) === 0){
					continue;
				}elseif($this->canFlowInto($x, $y - 1, $z)){
					return $accumulatedCost;
				}

				if($accumulatedCost >= 4){
					continue;
				}

				$realCost = $this->calculateFlowCost($x, $y, $z, $accumulatedCost + 1, $j);

				if($realCost < $cost){
					$cost = $realCost;
				}
			}
		}

		return $cost;
	}

	private function getOptimalFlowDirections($xx, $yy, $zz){
		$flowCost = [0, 0, 0, 0];
		$isOptimalFlowDirection = [0, 0, 0, 0];
		for($j = 0; $j < 4; ++$j){
			$flowCost[$j] = 1000;

			$x = $xx;
			$y = $yy;
			$z = $zz;

			if($j === 0){
				--$x;
			}elseif($j === 1){
				++$x;
			}elseif($j === 2){
				--$z;
			}elseif($j === 3){
				++$z;
			}

			if(!$this->canFlowInto($x, $y, $z)){
				continue;
			}elseif($this->canFlowInto($x, $y, $z) and $this->level->getBlockDataAt($x, $y, $z) === 0){
				continue;
			}elseif($this->canFlowInto($x, $y - 1, $z)){
				$flowCost[$j] = 0;
			}else{
				$flowCost[$j] = $this->calculateFlowCost($x, $y, $z, 1, $j);
			}
		}

		$minCost = $flowCost[0];

		for($i = 1; $i < 4; ++$i){
			if($flowCost[$i] < $minCost){
				$minCost = $flowCost[$i];
			}
		}

		for($i = 0; $i < 4; ++$i){
			$isOptimalFlowDirection[$i] = ($flowCost[$i] === $minCost);
		}

		return $isOptimalFlowDirection;
	}

	private function getSmallestFlowDecay($x1, $y1, $z1, $x2, $y2, $z2, $decay){
		$blockDecay = $this->getFlowDecay($x1, $y1, $z1, $x2, $y2, $z2);

		if($blockDecay < 0){
			return $decay;
		}elseif($blockDecay === 0){
			//Nothing to do!
		}elseif($blockDecay >= 8){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}


	private function canNetherLavaStay($x, $y, $z){
		$b = $this->level->getBlockIdAt($x, $y, $z);
		return $b === Block::AIR;
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 127; $y >= 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b == Block::AIR){
				break;
			}
		}

		return $y === 0 ? -1 : $y;
	}
}