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
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\normal\biome\WateryBiome;
use pocketmine\utils\Random;

class Caves extends Populator{

	/** @var Simplex */
	private $cavesSimplex = null;

	public function initPopulate(Random $random){
		if($this->cavesSimplex != null){
			return;
		}
		$this->cavesSimplex = new Simplex($random, 4.1, 15, 1 / 200);
		//实在太密啦啦啦
	}


	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$this->initPopulate($random);
		$chunk = $level->getChunk($chunkX, $chunkZ);
		$cavesGenerate = Generator::getFastNoise3D($this->cavesSimplex, 16, 128, 16, 4, 4, 4, $chunkX * 16, 0, $chunkZ * 16);
		for($x = 0; $x < 16; $x++){
			for($z = 0; $z < 16; $z++){

				$biome = Biome::getBiome($chunk->getBiomeId($x, $z));
				$hasWater = false;
				$highest = true;
				if($biome instanceof WateryBiome){
					$hasWater = true;
					$highest = false;
				}
				for($y = 127; $y >= 20; $y--){
					if($chunk->getBlockId($x, $y, $z) == Block::AIR){
						continue;
					}
					if($chunk->getBlockId($x, $y, $z) == Block::WATER or $chunk->getBlockId($x, $y, $z) == Block::STILL_WATER){
						$hasWater = true;
						$highest = false;
						continue;
					}
					if($hasWater){
						$y -= 5;
						$hasWater = false;
						continue;
					}
					if($cavesGenerate[$x][$z][$y] > 0.35){
						if($y > 20){
							$chunk->setBlockId($x, $y, $z, Block::AIR);
							$highest = $chunk->getHighestBlockAt($x, $z);
							/*int light = y < highest ? (highest - y < 10 ? highest - y : 1)  : 10;
							chunk.setBlockSkyLight(x, y, z, light);
							int bl = 0;
							if (y < 25) {
								bl = (25 - y) * 2;
							}
							chunk.setBlockLight(x, y, z, bl);*/
						}else{
							//LAVA
							$chunk->setBlockId($x, $y, $z, Block::STILL_LAVA);
							$chunk->setBlockLight($x, $y + 1, $z, 15);
						}

					}elseif($highest){
						if($chunk->getBlockId($x, $y, $z) == Block::DIRT){
							$chunk->setBlockId($x, $y, $z, Block::GRASS);
						}
						$highest = false;
					}
				}
			}
		}
	}
}
