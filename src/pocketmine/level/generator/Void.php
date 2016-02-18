<?php

namespace Void;

/*
一个虚空生成器,还没测试过==
仿照了下MUedua的SmallLandGenerator
*/
use pocketmine\level\generator\Generator;
use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\format\FullChunk;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Void extends Generator{

	private $level;
	private $chunk1,$chunk2,$chunk3,$chunk4,$chunk5,$chunk6,$chunk7,$chunk8,$chunk9;
	private $random;
	private $populators = [];

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
		$CX = ($chunkX % 3) < 0 ? (($chunkX % 3) + 3) : ($chunkX % 3);
		$CZ = ($chunkZ % 3) < 0 ? (($chunkZ % 3) + 3) : ($chunkZ % 3);
		switch ($CX.":".$CZ) {
			case '0:0':
				if ($this->chunk1 === null) {
					$this->chunk1 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x < 4 OR $z < 4) {
									$this->chunk1->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x > 4 AND $z > 4){
									$this->chunk1->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk1->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk1->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk1;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '0:1':
				if ($this->chunk2 === null) {
					$this->chunk2 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x < 4) {
									$this->chunk2->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x > 4){
									$this->chunk2->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk2->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk2->setBlockId($x, $y + 1, $z, Block::AIR);
									}

								}
							}
						}
					}
				}
				$chunk = clone $this->chunk2;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '0:2':
				if ($this->chunk3 === null) {
					$this->chunk3 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x < 4 OR $z > 11) {
									$this->chunk3->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x > 4 AND $z <11){
									$this->chunk3->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk3->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk3->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk3;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '1:0':
				if ($this->chunk4 === null) {
					$this->chunk4 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($z < 4) {
									$this->chunk4->setBlockId($x, $y, $z, Block::AIR);
								}elseif($z > 4){
									$this->chunk4->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk4->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk4->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk4;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:0':
				if ($this->chunk5 === null) {
					$this->chunk5 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x > 11 OR $z < 4) {
									$this->chunk5->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x < 11 AND $z > 4){
									$this->chunk5->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk5->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk5->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk5;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:1':
				if ($this->chunk6 === null) {
					$this->chunk6 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x > 11) {
									$this->chunk6->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x < 11){
									$this->chunk6->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk6->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk6->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk6;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '2:2':
				if ($this->chunk7 === null) {
					$this->chunk7 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($x > 11 OR $z > 11) {
									$this->chunk7->setBlockId($x, $y, $z, Block::AIR);
								}elseif($x < 11 AND $z < 11){
									$this->chunk7->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk7->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk7->setBlockId($x, $y + 1, $z, Block::AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk7;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			case '1:2':
				if ($this->chunk8 === null) {
					$this->chunk8 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								if ($z > 11) {
									$this->chunk8->setBlockId($x, $y, $z, Block::AIR);
								}elseif($z < 11){
									$this->chunk8->setBlockId($x, $y, $z, Block::AIR);
								}else{
									$this->chunk8->setBlockId($x, $y, $z, Block::AIR);
									if ($y == 63) {
										$this->chunk8->setBlockId($x, $y + 1, $z, AIR);
									}
								}
							}
						}
					}
				}
				$chunk = clone $this->chunk8;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;

			default:
				if ($this->chunk9 === null) {
					$this->chunk9 = clone $this->level->getChunk($chunkX, $chunkZ);
					for ($x=0; $x < 16; $x++) { 
						for ($z=0; $z < 16; $z++) { 
							for ($y=0; $y < 64; $y++) { 
								$this->chunk9->setBlockId($x, $y, $z, Block::AIR);
							}
						}
					}
				}
				$chunk = clone $this->chunk9;
				$chunk->setX($chunkX);
				$chunk->setZ($chunkZ);
				$this->level->setChunk($chunkX, $chunkZ, $chunk);
				break;
		}
	}

	public function populateChunk($chunkX, $chunkZ){

	}

	public function getSpawn(){
		return new Vector3(128, 72, 128);
	}

}
