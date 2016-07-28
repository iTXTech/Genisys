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

namespace pocketmine\level\generator\populator;

use pocketmine\utils\Random;

abstract class VariableAmountPopulator extends Populator{
	protected $baseAmount;
	protected $randomAmount;

	public function __construct(int $baseAmount = 0, int $randomAmount = 0){
		$this->baseAmount = $baseAmount;
		$this->randomAmount = $randomAmount;
	}

	public function getAmount(Random $random){
		return $this->getAmount($random);
	}

	public final function setBaseAmount(int $baseAmount){
		$this->baseAmount = $baseAmount;
	}

	public final function setRandomAmount(int $randomAmount){
		$this->randomAmount = $randomAmount;
	}

	public function getBaseAmount() : int{
		return $this->baseAmount;
	}

	public function getRandomAmount() : int{
		return $this->randomAmount;
	}
}