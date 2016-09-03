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

class BlockLayout{
	private $layout = [];
	private $style = [];

	public function __construct(string $layout){
		$lines = explode("\n", $layout);
		$row = 0;
		foreach($lines as $line){
			for($col = 0; $col < strlen($line); $col++){
				$this->layout[$row][$col] = ord($line{$col});
			}
			$row++;
		}
		$this->style["."] = "";
	}

	public function setBlockId($key, int $id){
		$this->style[$key] = $id;
	}

	public function getRowLength() : int{
		return count($this->layout);
	}

	public function getColumnLength(int $row) : int{
		return count($this->layout[$row]);
	}

	public function getBlockId(int $row, int $column){
		if(isset($this->style[$this->layout[$row][$column]])){
			return $this->style[$this->layout[$row][$column]];
		}
		return null;
	}
}