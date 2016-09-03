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

use pocketmine\block\Block;

class SimpleBlockPicker implements BlockPicker{
	private $outer;
	private $inner;

	public function __construct(int $outer = Block::AIR, int $inner = Block::AIR){
		$this->outer = $outer;
		$this->inner = $inner;
	}

	public function setOuter(int $id){
		$this->outer = $id;
	}

	public function setInner(int $id){
		$this->inner = $id;
	}

	public function setOuterInner($outer, $inner){
		$this->setOuter($outer);
		$this->setInner($inner);
	}

	public function get(bool $outer) : int{
		return $outer ? $this->outer : $this->inner;
	}

	public function getInner() : int{
		return $this->inner;
	}

	public function getOuter() : int{
		return $this->outer;
	}
}