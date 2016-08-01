<?php

/**
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\inventory\transaction\DropItemTransaction;
use pocketmine\item\Item;

class SimpleTransactionQueue implements TransactionQueue{
	
	/** @var Player[] */
	protected $player = null;
	
	/** @var \SplQueue */
	protected $transactionQueue;
	/** @var \SplQueue */
	protected $transactionsToRetry;
	
	/** @var float */
	protected $lastUpdate = -1;
	
	/** @var int */
	protected $transactionCount = 0;
	
	/**
	 * @param Player $player
	 */
	public function __construct(Player $player = null){
		$this->player = $player;
		$this->transactionQueue = new \SplQueue();
		$this->transactionsToRetry = new \SplQueue();
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	public function getTransactionCount(){
		return $this->transactionCount;
	}
	
	/**
	 * @return \SplQueue
	 */
	public function getTransactions(){
		return $this->transactionQueue;
	}
	
	/**
	 * @param Transaction $transaction
	 *
	 * Adds a transaction to the queue
	 */
	public function addTransaction(Transaction $transaction){
		$this->transactionQueue->enqueue($transaction);
		$this->lastUpdate = microtime(true);
		$this->transactionCount += 1;
	}
	
	/** 
	 * @param Transaction 	$transaction
	 * @param Transaction[] &$failed
	 *
	 * Handles a failed transaction
	 */
	private function handleFailure(Transaction $transaction, &$failed){
		$transaction->addFailure();
		if($transaction->getFailures() >= self::DEFAULT_ALLOWED_RETRIES){
			/* Transaction failed after several retries */
			$failed[] = $transaction;
		}else{
			/* Add the transaction to the back of the queue to be retried on the next tick */
			$this->transactionsToRetry->enqueue($transaction);
		}
	}
	
	/**
	 * @return bool
	 *
	 * Handles transaction queue execution
	 */
	public function execute(){
		
		/** @var Transaction[] */
		$failed = [];
		
		/*$this->isExecuting = true;
		
		$failCount = $this->transactionsToRetry->count();*/

		while(!$this->transactionsToRetry->isEmpty()){
			//Some failed transactions are waiting from the previous execution to be retried
			$this->transactionQueue->enqueue($this->transactionsToRetry->dequeue());
		}
		
		/*if($this->transactionQueue->count() !== 0){
			echo "Batch-handling ".$this->transactionQueue->count()." changes, with ".$failCount." retries.\n";
		}*/
		
		while(!$this->transactionQueue->isEmpty()){
			
			$transaction = $this->transactionQueue->dequeue();
				
			$change = $transaction->getChange();

			if($change["out"] instanceof Item){
				if(!$this->player->getServer()->allowInventoryCheats){
					if($transaction->getInventory()->slotContains($transaction->getSlot(), $change["out"]) and !$this->player->isCreative()){
						//Do not add items to the crafting inventory in creative to prevent weird duplication bugs.
						$this->player->getFloatingInventory()->addItem($change["out"]);
						
					}elseif(!$this->player->isCreative()){ //Transaction failed, if the player is not in creative then this needs to be retried.
						$this->handleFailure($transaction, $failed);
						continue;
					}
				}
				$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
			}
			if($change["in"] instanceof Item){
				if(!$this->player->getServer()->allowInventoryCheats){
					if($this->player->getFloatingInventory()->contains($change["in"]) and !$this->player->isCreative()){
						$this->player->getFloatingInventory()->removeItem($change["in"]);
						
					}elseif(!$this->player->isCreative()){ //Transaction failed, if the player was not creative then transaction is illegal
						$this->handleFailure($transaction, $failed);
						continue;
					}
				}
				
				if($transaction instanceof DropItemTransaction){
					$this->player->dropItem($transaction->getTargetItem());
				}else{
					$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
				}
			}
			$this->transactionCount -= 1;
			$transaction->setSuccess();
			$transaction->sendSlotUpdate($this->player);
		}

		foreach($failed as $f){
			$f->sendSlotUpdate($this->player);
		}
		

		return true;
	}
}