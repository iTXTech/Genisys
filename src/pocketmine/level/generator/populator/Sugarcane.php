<?php


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

			if($y !== -1 and $this->canSugarcaneStay($x, $y, $z)){
				$this->level->setBlockIdAt($x, $y, $z, Block::SUGARCANE_BLOCK);
				$this->level->setBlockDataAt($x, $y, $z, 1);
			}
		}
	}

	private function canSugarCaneStay($x, $y, $z)
    {
             $b = $this->level->getBlockIdAt($x, $y, $z);
        return ($b === Block::GRASS or $b === Block::SAND and $this->level->getBlockIdAt($x -1, $y, $z) or $this->level->getBlockIdAt($x, $y, $z -1) or  $this->level->getBlockIdAt($x, $y, $z +1) or  $this->level->getBlockIdAt($x +1, $y, $z) === Block::STILL_WATER)
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
