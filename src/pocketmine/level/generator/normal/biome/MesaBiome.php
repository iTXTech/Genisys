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

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\StainedClay;
use pocketmine\level\generator\populator\Cactus;
use pocketmine\level\generator\populator\DeadBush;

class MesaBiome extends SandyBiome{

	public function __construct(){
		parent::__construct();

		$cactus = new Cactus();
		$cactus->setBaseAmount(0);
		$cactus->setRandomAmount(5);
		$deadBush = new DeadBush();
		$cactus->setBaseAmount(2);
		$deadBush->setRandomAmount(10);

		$this->addPopulator($cactus);
		$this->addPopulator($deadBush);

		$this->setElevation(63, 81);

		$this->temperature = 2.0;
		$this->rainfall = 0.8;
		$this->setGroundCover([
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_PINK),
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_ORANGE),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_BLACK),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_GRAY),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_WHITE),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_ORANGE),
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::HARDENED_CLAY, 0),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_YELLOW),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_BLACK),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_PINK),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_PINK),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::STAINED_CLAY, StainedClay::CLAY_WHITE),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
			Block::get(Block::RED_SANDSTONE, 0),
		]);
	}

	public function getName() : string{
		return "Mesa";
	}
}