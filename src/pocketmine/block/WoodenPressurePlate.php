<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:43
 ]

 */
namespace pocketmine\block;

class WoodenPressurePlate extends PressurePlate{
	protected $id = self::WOODEN_PRESSURE_PLATE;

	public function getName() : string{
		return "Wooden Pressure Plate";
	}
}