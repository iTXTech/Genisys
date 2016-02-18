<?php

namespace pocketmine\level\generator;



use pocketmine\block\Block;
use pocketmine\level\ChunkManager;

use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Void extends Generator{

	private $level;
	private $chunk;
	private $random;


	public function getSettings(){
		return [];
	}

	public function getName(){
		return "Void";
	}

	public function __construct(array $settings = []){
		$this->options = $settings;
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
	}

	public function generateChunk($chunkX, $chunkZ){
		if ($this->chunk === null) {
			$this->chunk = clone $this->level->getChunk($chunkX, $chunkZ);
			for ($x=0; $x < 16; $x++) {
				for ($z=0; $z < 16; $z++) {
					for ($y=0; $y < 64; $y++) {
						$this->chunk->setBlockId($x, $y, $z, Block::AIR);
					}
				}
			}
		}
		$chunk = clone $this->chunk;
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX, $chunkZ, $chunk);

	}

	public function populateChunk($chunkX, $chunkZ){

	}

	public function getSpawn(){
		return new Vector3(128, 72, 128);
	}

}
