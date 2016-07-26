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
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\math\VectorMath;
use pocketmine\utils\Random;

class Cave extends Populator{
	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$overLap = 8;
		$firstSeed = $random->nextInt();
		$secondSeed = $random->nextInt();
		for($cxx = 0; $cxx < 1; $cxx++){
			for($czz = 0; $czz < 1; $czz++){
				$dcx = $chunkX + $cxx;
				$dcz = $chunkZ + $czz;
				for($cxxx = -$overLap; $cxxx <= $overLap; $cxxx++){
					for($czzz = -$overLap; $czzz <= $overLap; $czzz++){
						$dcxx = $dcx + $cxxx;
						$dczz = $dcz + $czzz;
						$this->pop($level, $dcxx, $dczz, $dcx, $dcz, new Random(($dcxx * $firstSeed) ^ ($dczz * $secondSeed) ^ $random->getSeed()));
					}
				}
			}
		}
	}

	private function pop(ChunkManager $level, $x, $z, $chunkX, $chunkZ, Random $random){
		$c = $level->getChunk($x, $z);
		$oC = $level->getChunk($chunkX, $chunkZ);
		if($c == null or $oC == null or ($c != null and !$c->isGenerated()) or ($oC != null and !$oC->isGenerated())){
			return;
		}
		$chunk = new Vector3($x << 4, 0, $z << 4);
		$originChunk = new Vector3($chunkX << 4, 0, $chunkZ << 4);
		if($random->nextBoundedInt(15) != 0){
			return;
		}

		$numberOfCaves = $random->nextBoundedInt($random->nextBoundedInt($random->nextBoundedInt(40) + 1) + 1);
		for($caveCount = 0; $caveCount < $numberOfCaves; $caveCount++){
			$target = new Vector3($chunk->getX() + $random->nextBoundedInt(16), $random->nextBoundedInt($random->nextBoundedInt(120) + 8), $chunk->getZ() + $random->nextBoundedInt(16));

			$numberOfSmallCaves = 1;

			if($random->nextBoundedInt(4) == 0){
				$this->generateLargeCaveBranch($level, $originChunk, $target, new Random($random->nextInt()));
				$numberOfSmallCaves += $random->nextBoundedInt(4);
			}

			for($count = 0; $count < $numberOfSmallCaves; $count++){
				$randomHorizontalAngle = $random->nextFloat() * pi() * 2;
				$randomVerticalAngle = (($random->nextFloat() - 0.5) * 2) / 8;
				$horizontalScale = $random->nextFloat() * 2 + $random->nextFloat();

				if($random->nextBoundedInt(10) == 0){
					$horizontalScale *= $random->nextFloat() * $random->nextFloat() * 3 + 1;
				}

				$this->generateCaveBranch($level, $originChunk, $target, $horizontalScale, 1, $randomHorizontalAngle, $randomVerticalAngle, 0, 0, new Random($random->nextInt()));
			}
		}
	}

	private function generateCaveBranch(ChunkManager $level, Vector3 $chunk, Vector3 $target, $horizontalScale, $verticalScale, $horizontalAngle, $verticalAngle, int $startingNode, int $nodeAmount, Random $random){
		$middle = new Vector3($chunk->getX() + 8, 0, $chunk->getZ() + 8);
		$horizontalOffset = 0;
		$verticalOffset = 0;

		if($nodeAmount <= 0){
			$size = 7 * 16;
			$nodeAmount = $size - $random->nextBoundedInt($size / 4);
		}

		$intersectionMode = $random->nextBoundedInt($nodeAmount / 2) + $nodeAmount / 4;
		$extraVerticalScale = $random->nextBoundedInt(6) == 0;

		if($startingNode == -1){
			$startingNode = $nodeAmount / 2;
			$lastNode = true;
		}else{
			$lastNode = false;
		}

		for(; $startingNode < $nodeAmount; $startingNode++){
			$horizontalSize = 1.5 + sin($startingNode * pi() / $nodeAmount) * $horizontalScale;
			$verticalSize = $horizontalSize * $verticalScale;
			$target = $target->add(VectorMath::getDirection3D($horizontalAngle, $verticalAngle));
			if($extraVerticalScale){
				$verticalAngle *= 0.92;
			}else{
				$verticalScale *= 0.7;
			}

			$verticalAngle += $verticalOffset * 0.1;
			$horizontalAngle += $horizontalOffset * 0.1;
			$verticalOffset *= 0.9;
			$horizontalOffset *= 0.75;
			$verticalOffset += ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 2;
			$horizontalOffset += ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 4;

			if(!$lastNode){
				if($startingNode == $intersectionMode and $horizontalScale > 1 and $nodeAmount > 0){
					$this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 0.5 + 0.5, 1, $horizontalAngle - pi() / 2, $verticalAngle / 3, $startingNode, $nodeAmount, new Random($random->nextInt()));
					$this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 0.5 + 0.5, 1, $horizontalAngle - pi() / 2, $verticalAngle / 3, $startingNode, $nodeAmount, new Random($random->nextInt()));
					return;
				}

				if($random->nextBoundedInt(4) == 0){
					continue;
				}
			}

			$xOffset = $target->getX() - $middle->getX();
			$zOffset = $target->getZ() - $middle->getZ();
			$nodesLeft = $nodeAmount - $startingNode;
			$offsetHorizontalScale = $horizontalScale + 18;

			if((($xOffset * $xOffset + $zOffset * $zOffset) - $nodesLeft * $nodesLeft) > ($offsetHorizontalScale * $offsetHorizontalScale)){
				return;
			}

			if($target->getX() < ($middle->getX() - 16 - $horizontalSize * 2)
				or $target->getZ() < ($middle->getZ() - 16 - $horizontalSize * 2)
				or $target->getX() > ($middle->getX() + 16 + $horizontalSize * 2)
				or $target->getZ() > ($middle->getZ() + 16 + $horizontalSize * 2)
			){
				continue;
			}

			$start = new Vector3(floor($target->getX() - $horizontalSize) - $chunk->getX() - 1, floor($target->getY() - $verticalSize) - 1, floor($target->getZ() - $horizontalSize) - $chunk->getZ() - 1);
			$end = new Vector3(floor($target->getX() + $horizontalSize) - $chunk->getX() + 1, floor($target->getY() + $verticalSize) + 1, floor($target->getZ() + $horizontalSize) - $chunk->getZ() + 1);
			$node = new CaveNode($level, $chunk, $start, $end, $target, $verticalSize, $horizontalSize);

			if($node->canPlace()){
				$node->place();
			}

			if($lastNode){
				break;
			}
		}
	}

	private function generateLargeCaveBranch(ChunkManager $level, Vector3 $chunk, Vector3 $target, Random $random){
		$this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 6 + 1, 0.5, 0, 0, -1, -1, $random);
	}
}

class CaveNode{
	/** @var ChunkManager */
	private $level;
	/** @var Vector3 */
	private $chunk;
	/** @var Vector3 */
	private $start;
	/** @var Vector3 */
	private $end;
	/** @var Vector3 */
	private $target;
	private $verticalSize;
	private $horizontalSize;

	public function __construct(ChunkManager $level, Vector3 $chunk, Vector3 $start, Vector3 $end, Vector3 $target, $verticalSize, $horizontalSize){
		$this->level = $level;
		$this->chunk = $chunk;
		$this->start = $this->clamp($start);
		$this->end = $this->clamp($end);
		$this->target = $target;
		$this->verticalSize = $verticalSize;
		$this->horizontalSize = $horizontalSize;
	}

	private function clamp(Vector3 $pos){
		return new Vector3(
			Math::clamp($pos->getFloorX(), 0, 16),
			Math::clamp($pos->getFloorY(), 1, 120),
			Math::clamp($pos->getFloorZ(), 0, 16)
		);
	}

	public function canPlace(){
		for($x = $this->start->getFloorX(); $x < $this->end->getFloorX(); $x++){
			for($z = $this->start->getFloorZ(); $z < $this->end->getFloorZ(); $z++){
				for($y = $this->end->getFloorY() + 1; $y >= $this->start->getFloorY() - 1; $y--){
					$blockId = $this->level->getBlockIdAt($this->chunk->getX() + $x, $y, $this->chunk->getZ() + $z);
					if($blockId == Block::WATER or $blockId == Block::STILL_WATER){
						return false;
					}
					if($y != ($this->start->getFloorY() - 1) and $x != ($this->start->getFloorX()) and $x != ($this->end->getFloorX() - 1) and $z != ($this->start->getFloorZ()) and $z != ($this->end->getFloorZ() - 1)){
						$y = $this->start->getFloorY();
					}
				}
			}
		}
		return true;
	}

	public function place(){
		for($x = $this->start->getFloorX(); $x < $this->end->getFloorX(); $x++){
			$xOffset = ($this->chunk->getX() + $x + 0.5 - $this->target->getX()) / $this->horizontalSize;
			for($z = $this->start->getFloorZ(); $z < $this->end->getFloorZ(); $z++){
				$zOffset = ($this->chunk->getZ() + $z + 0.5 - $this->target->getZ()) / $this->horizontalSize;
				if(($xOffset * $xOffset + $zOffset * $zOffset) >= 1){
					continue;
				}
				for($y = $this->end->getFloorY() - 1; $y >= $this->start->getFloorY(); $y--){
					$yOffset = ($y + 0.5 - $this->target->getY()) / $this->verticalSize;
					if($yOffset > -0.7 and ($xOffset * $xOffset + $yOffset * $yOffset + $zOffset * $zOffset) < 1){
						$xx = $this->chunk->getX() + $x;
						$zz = $this->chunk->getZ() + $z;
						$blockId = $this->level->getBlockIdAt($xx, $y, $zz);
						if($blockId == Block::STONE or $blockId == Block::DIRT or $blockId == Block::GRASS){
							if($y < 10){
								$this->level->setBlockIdAt($xx, $y, $zz, Block::STILL_LAVA);
							}else{
								if($blockId == Block::GRASS and $this->level->getBlockIdAt($xx, $y - 1, $zz) == Block::DIRT){
									$this->level->setBlockIdAt($xx, $y - 1, $zz, Block::GRASS);
								}
								$this->level->setBlockIdAt($xx, $y, $zz, Block::AIR);
							}
						}
					}
				}
			}
		}
	}
}