<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:43
 ]

 */
namespace pocketmine\block;

class LightWeightedPressurePlate extends PressurePlate{
	protected $id = self::LIGHT_WEIGHTED_PRESSURE_PLATE;

	public function getName() : string{
		return "Light Weighted Pressure Plate";
	}
}