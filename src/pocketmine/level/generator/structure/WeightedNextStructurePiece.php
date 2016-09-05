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

namespace pocketmine\level\generator\structure;

use pocketmine\math\MathHelper;

abstract class WeightedNextStructurePiece extends StructurePiece{
	private $weightedPieces = [];

	public function __construct(Structure $parent, WeightedNextPieceCache $defaults = null){
		parent::__construct($parent);
		if($defaults != null){
			$this->addNextPieces($defaults);
		}
	}

	public function addNextPieces(WeightedNextPieceCache $cache){
		foreach($cache->getContents() as $piece){
			$this->weightedPieces[] = $piece;
		}
	}

	public function addNextPiece(StructurePiece $piece, int $weight){
		$this->weightedPieces[] = [get_class($piece), $weight];
	}

	protected function getNextPiece() : StructurePiece{
		$class = MathHelper::chooseWeightedRandom($this->getRandom(), $this->weightedPieces);
		if($class != null){
			return new $class($this->parent);
		}
		return null;
	}

}