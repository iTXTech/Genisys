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

use pocketmine\level\generator\normal\populator\Cactus;
use pocketmine\level\generator\normal\populator\DeadBush;

class BeachBiome extends SandyBiome{

	public function __construct(){
		parent::__construct();

		$this->removePopulator(new Cactus());
		$this->removePopulator(new DeadBush());
		
		$this->setElevation(62, 65);
	}

	public function getName() : string{
		return "Beach";
	}
}