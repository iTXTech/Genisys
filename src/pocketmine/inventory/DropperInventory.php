<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/3 14:30


 *
 * OpenGenisys Project
 */
namespace pocketmine\inventory;

use pocketmine\tile\Dropper;

class DropperInventory extends ContainerInventory{
	public function __construct(Dropper $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::DROPPER));
	}

	/**
	 * @return Dropper
	 */
	public function getHolder(){
		return $this->holder;
	}
}