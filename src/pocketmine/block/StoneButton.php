<?php
/**
 * Author: PeratX
 * Time: 2015/12/20 20:14
 ]

 */
namespace pocketmine\block;

class StoneButton extends WoodenButton{
	protected $id = self::STONE_BUTTON;

	public function getName() : string{
		return "Stone Button";
	}
}