<?php
/**
 * Author: PeratX
 * Time: 2015/12/22 21:12
 ]

 */
namespace pocketmine\block;

class UnlitRedstoneTorch extends RedstoneTorch{
	protected $id = self::UNLIT_REDSTONE_TORCH;

	public function getLightLevel(){
		return 0;
	}

	public function isActivated(){
		return false;
	}
}