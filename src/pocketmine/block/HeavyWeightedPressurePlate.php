<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:43
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

class HeavyWeightedPressurePlate extends PressurePlate{
	protected $id = self::HEAVY_WEIGHTED_PRESSURE_PLATE;

	public function getName(){
		return "Heavy Weighted Pressure Plate";
	}
}