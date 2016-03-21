<?php
/**
 * Author: PeratX
 * Time: 2015/12/13 19:34
 ]

 */

namespace pocketmine\block;

class InactiveRedstoneLamp extends ActiveRedstoneLamp{
	protected $id = self::INACTIVE_REDSTONE_LAMP;

	public function getLightLevel(){
		return 0;
	}

	public function getName() : string{
		return "Inactive Redstone Lamp";
	}

	public function isLightedByAround(){
		return false;
	}

	public function turnOn(){
		//if($isLightedByAround){
		$this->getLevel()->setBlock($this, new ActiveRedstoneLamp(), true, false);
		/*}else{
			$this->getLevel()->setBlock($this, new ActiveRedstoneLamp(), true, false);
			//$this->lightAround();
		}*/
		return true;
	}

	public function turnOff(){
		return true;
	}
}