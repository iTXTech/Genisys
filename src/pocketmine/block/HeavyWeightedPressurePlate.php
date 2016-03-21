<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:43
 ]

 */
namespace pocketmine\block;

class HeavyWeightedPressurePlate extends PressurePlate{
	protected $id = self::HEAVY_WEIGHTED_PRESSURE_PLATE;

	public function getName() : string{
		return "Heavy Weighted Pressure Plate";
	}
}