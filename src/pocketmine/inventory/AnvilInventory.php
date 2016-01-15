<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\inventory;

use pocketmine\level\Position;
use pocketmine\Player;

class AnvilInventory extends ContainerInventory{
	public function __construct(Position $pos){
		parent::__construct(new FakeBlockMenu($this, $pos), InventoryType::get(InventoryType::ANVIL));
	}

	/**
	 * @return FakeBlockMenu
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function hasSource(){
		if($this->getItem(0)->getId() != 0 or $this->getItem(1)->getId() != 0) return true;
		return false;
	}
	
	/*public function sendResult(Player $p){
		$item = $this->getResult();
		if($item->equals($this->getItem(0),true,false)) $this->setItem(0,new Item(0));
		if($item->equals($this->getItem(1),true,false)) $this->setItem(1,new Item(0));
		$p->getInventory()->addItem($item);
		$this->setResult(new Item(0));
	}*/

	public function onClose(Player $who){
		$who->updateExperience();
		parent::onClose($who);
		
		$this->getHolder()->getLevel()->dropItem($this->getHolder()->add(0.5, 0.5, 0.5), $this->getItem(1));
		$this->getHolder()->getLevel()->dropItem($this->getHolder()->add(0.5, 0.5, 0.5), $this->getItem(0));

		$this->clear(0);
		$this->clear(1);
		$this->clear(2);
	}
}