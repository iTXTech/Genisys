<?php


namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;

class IcePlainsBiome extends OceanBiome{

	public function __construct(){
		parent::__construct();

		$this->setElevation(55, 127);

		$this->temperature = 0.05;
   $this->rainfall = 0.8;
   $this->setGroundCover([
			Block::get(Block::STAINED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, 1),
			Block::get(Block::STAINED_CLAY, 1),
			Block::get(Block::STAINED_CLAY, 2),
			Block::get(Block::STAINED_CLAY, 2),
			Block::get(Block::STAINED_CLAY, 3),
			Block::get(Block::STAINED_CLAY, 3),
			Block::get(Block::STAINED_CLAY, 4),
			Block::get(Block::STAINED_CLAY, 4),
			Block::get(Block::STAINED_CLAY, 5),
			Block::get(Block::STAINED_CLAY, 5),
			Block::get(Block::STAINED_CLAY, 6),
			Block::get(Block::STAINED_CLAY, 6),
			Block::get(Block::STAINED_CLAY, 7),
			Block::get(Block::STAINED_CLAY, 7),
			Block::get(Block::STAINED_CLAY, 8),
			Block::get(Block::STAINED_CLAY, 8),
			Block::get(Block::STAINED_CLAY, 9),
			Block::get(Block::STAINED_CLAY, 9),
			Block::get(Block::STAINED_CLAY, 10),
			Block::get(Block::STAINED_CLAY, 10),
			Block::get(Block::STAINED_CLAY, 11),
			Block::get(Block::STAINED_CLAY, 11),
			Block::get(Block::STAINED_CLAY, 12),
			Block::get(Block::STAINED_CLAY, 12),
			Block::get(Block::STAINED_CLAY, 13),
			Block::get(Block::STAINED_CLAY, 13),
			Block::get(Block::STAINED_CLAY, 14),
			Block::get(Block::STAINED_CLAY, 14),
			Block::get(Block::STAINED_CLAY, 15),
			Block::get(Block::STAINED_CLAY, 15),
			Block::get(Block::STAINED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, 1),
			Block::get(Block::STAINED_CLAY, 1),
			Block::get(Block::STAINED_CLAY, 2),
			Block::get(Block::STAINED_CLAY, 2),
			Block::get(Block::STAINED_CLAY, 3),
			Block::get(Block::STAINED_CLAY, 3),
			Block::get(Block::STAINED_CLAY, 4),
			Block::get(Block::STAINED_CLAY, 4),
			Block::get(Block::STAINED_CLAY, 5),
			Block::get(Block::STAINED_CLAY, 5),
			Block::get(Block::STAINED_CLAY, 6),
			Block::get(Block::STAINED_CLAY, 6),
			Block::get(Block::STAINED_CLAY, 7),
			Block::get(Block::STAINED_CLAY, 7),
			Block::get(Block::STAINED_CLAY, 8),
			Block::get(Block::STAINED_CLAY, 8),
			Block::get(Block::STAINED_CLAY, 9),
			Block::get(Block::STAINED_CLAY, 9),
			Block::get(Block::STAINED_CLAY, 10),
			Block::get(Block::STAINED_CLAY, 10),
			Block::get(Block::STAINED_CLAY, 11),
			Block::get(Block::STAINED_CLAY, 11),
			Block::get(Block::STAINED_CLAY, 12),
			Block::get(Block::STAINED_CLAY, 12),
			Block::get(Block::STAINED_CLAY, 13),
			Block::get(Block::STAINED_CLAY, 13),
			Block::get(Block::STAINED_CLAY, 14),
			Block::get(Block::STAINED_CLAY, 14),
			Block::get(Block::STAINED_CLAY, 15),
			Block::get(Block::STAINED_CLAY, 15),
		]); 
	}

	public function getName() : string{
		return "Mesa";
	}
}
