<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/3 14:30


 *
 * OpenGenisys Project
 */
namespace pocketmine\inventory;

use pocketmine\tile\Dispenser;

class DispenserInventory extends ContainerInventory{
	public function __construct(Dispenser $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::DISPENSER));
	}

	/**
	 * @return Dispenser
	 */
	public function getHolder(){
		return $this->holder;
	}
}