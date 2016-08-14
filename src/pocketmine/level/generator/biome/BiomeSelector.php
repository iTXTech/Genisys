<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\level\generator\biome;

use pocketmine\level\generator\noise\Simplex;
use pocketmine\utils\Random;

class BiomeSelector{

	/** @var Biome */
	private $fallback;

	/** @var Simplex */
	private $temperature;
	/** @var Simplex */
	private $rainfall;

	/** @var Biome[] */
	private $biomes = [];

	private $map = [];

	public function __construct(Random $random, Biome $fallback){
		$this->fallback = $fallback;
		$this->temperature = new Simplex($random, 2, 1 / 16, 1 / 512);
		$this->rainfall = new Simplex($random, 2, 1 / 16, 1 / 512);
	}

	public function lookup($temperature, $rainfall){
		if($rainfall < 0.25){
			if($temperature < 0.7){
				return Biome::OCEAN;
			}elseif($temperature < 0.85){
				return Biome::RIVER;
			}else{
				return Biome::SWAMP;
			}
		}elseif($rainfall < 0.60){
			if($temperature < 0.25){
				return Biome::ICE_PLAINS;
			}elseif($temperature < 0.75){
				return Biome::PLAINS;
			}else{
				return Biome::DESERT;
			}
		}elseif($rainfall < 0.80){
			if($temperature < 0.25){
				return Biome::TAIGA;
			}elseif($temperature < 0.75){
				return Biome::FOREST;
			}else{
				return Biome::BIRCH_FOREST;
			}
		}else{
			if($temperature < 0.25){
				return Biome::MOUNTAINS;
			}elseif($temperature < 0.70){
				return Biome::SMALL_MOUNTAINS;
			}elseif($temperature <= 2.0){
				return Biome::MESA;
			}else{
				return Biome::RIVER;
			}
		}
	}

	public function recalculate(){
		$this->map = new \SplFixedArray(64 * 64);

		for($i = 0; $i < 64; ++$i){
			for($j = 0; $j < 64; ++$j){
				$this->map[$i + ($j << 6)] = $this->lookup($i / 63, $j / 63);
			}
		}
	}

	public function addBiome(Biome $biome){
		$this->biomes[$biome->getId()] = $biome;
	}

	public function getTemperature($x, $z){
		return ($this->temperature->noise2D($x, $z, true) + 1) / 2;
	}

	public function getRainfall($x, $z){
		return ($this->rainfall->noise2D($x, $z, true) + 1) / 2;
	}

	/**
	 * @param $x
	 * @param $z
	 *
	 * @return Biome
	 */
	public function pickBiome($x, $z){
		$temperature = (int) ($this->getTemperature($x, $z) * 63);
		$rainfall = (int) ($this->getRainfall($x, $z) * 63);

		$biomeId = $this->map[$temperature + ($rainfall << 6)];
		return isset($this->biomes[$biomeId]) ? $this->biomes[$biomeId] : $this->fallback;
	}
}