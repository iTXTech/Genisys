<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\inventory\TransactionQueue;

/**
 * Called when an inventory transaction queue starts execution. 
 */

class InventoryTransactionEvent extends Event implements Cancellable{

	public static $handlerList = null;
	
	/** @var TransactionQueue */
	private $transactionQueue;
	
	/**
	 * @param TransactionQueue $ts
	 */
	public function __construct(TransactionQueue $transactionQueue){
		$this->transactionQueue = $transactionQueue;
	}

	/**
	 * @deprecated
	 * @return TransactionQueue
	 */
	public function getTransaction(){
		return $this->transactionQueue;
	}

	/**
	 * @return TransactionQueue
	 */
	public function getQueue(){
		return $this->transactionQueue;
	}
}