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

class WeightedNextPieceCache{
	private $weightedPieces = [];

	public function getContents(){
		return $this->weightedPieces;
	}

	public function addAll(array $pieces){
		foreach($pieces as $piece){
			$this->weightedPieces[] = $piece;
		}
		return $this;
	}

	public function add(StructurePiece $piece, int $weight){
		$this->weightedPieces[] = [get_class($piece), $weight];
		return $this;
	}

	public function clear(){
		$this->weightedPieces = [];
		return $this;
	}
}