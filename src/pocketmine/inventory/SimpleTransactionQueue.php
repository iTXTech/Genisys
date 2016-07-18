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
	
	const DEFAULT_ALLOWED_RETRIES = 20;
	//const MAX_QUEUE_LENGTH = 3;
	
	/** @var Player[] */
	protected $player = null;
	
	/** @var \SplQueue */
	protected $transactionQueue;
	/** @var \SplQueue */
	protected $transactionsToRetry;
	
	/** @var bool */
	protected $isExecuting = false;
	
	/** @var float */
	protected $lastUpdate = -1;
	
	/** @var Inventory[] */	
	protected $inventories = [];
	
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
	
	/**
	 * @return \SplQueue
	 */
	public function getTransactions(){
		return $this->transactionQueue;
	}
	
	/**
	 * @return Inventory[]
	 */
	/*public function getInventories(){
		return $this->inventories;
	}*/
	
	/**
	 * @return bool
	 */
	public function isExecuting(){
		return $this->isExecuting;
	}
	
	/**
	 * @param Transaction $transaction
	 * @return bool
	 *
	 * Adds a transaction to the queue
	 * Returns true if the addition was successful, false if not.
	 */
	public function addTransaction(Transaction $transaction){
		/*if($this->transactionQueue->count() >= self::MAX_QUEUE_LENGTH){
			//Max pending transactions already queued.
			echo "new transaction rejected\n";
			$transaction->sendSlotUpdate($this->player);
			return false;
		}*/
		
		$change = $transaction->getChange();
		if(@$change["in"] instanceof Item or @$change["out"] instanceof Item){
			$this->transactionQueue->enqueue($transaction);
			$this->lastUpdate = microtime(true);
		}else{
			//Null change detected, nothing needs to be done
			return false;
		}
		
		return true;
	}
	
	
	private $allowedRetries = 2;
	
	/** 
	 * @param Transaction 	$transaction
	 * @param Transaction[] &$completed
	 *
	 * Handles a failed transaction
	 */
	private function handleFailure(Transaction $transaction, &$failed){
		$transaction->addFailure();
		if($transaction->getFailures() >= $this->allowedRetries){
			//Transaction failed after several retries
			echo "transaction completely failed\n";
			$failed[] = $transaction;
		}else{
			//Add the transaction to the back of the queue to be retried
			$this->transactionsToRetry->enqueue($transaction);
		}
	}
	
	/**
	 * @return Transaction[] $failed | bool
	 *
	 * Handles transaction execution
	 * Returns an array of transactions which failed
	 */
	public function execute(){
		/*if($this->isExecuting()){
			echo "execution already in progress\n";
			return false;
		}elseif(microtime(true) - $this->lastUpdate < 0.05){
			//echo "last update time less than 10 ticks ago\n";
			return false;
		}*/
		
		/** @var Transaction[] */
		$failed = [];
		
		$this->isExecuting = true;
		
		$failCount = $this->transactionsToRetry->count();
		while(!$this->transactionsToRetry->isEmpty()){
			//Some failed transactions are waiting from the previous execution to be retried
			$this->transactionQueue->enqueue($this->transactionsToRetry->dequeue());
		}
		
		if($this->transactionQueue->count() !== 0){
			echo "Batch-handling ".$this->transactionQueue->count()." changes, with ".$failCount." retries.\n";
		}
		
		$this->allowedRetries = max(self::DEFAULT_ALLOWED_RETRIES, $this->transactionQueue->count()); //Statistically at least 50% of transactions will succeed
		
		while(!$this->transactionQueue->isEmpty()){
			
			$transaction = $this->transactionQueue->dequeue();
			
			if($transaction instanceof DropItemTransaction){ //Dropped item
				$droppedItem = $transaction->getTargetItem();
				if($this->player->getCraftingInventory()->contains($droppedItem) or $this->player->isCreative()){
					
					$this->player->getCraftingInventory()->removeItem($droppedItem);
					
					$transaction->setSuccess();
					$this->player->dropItem($droppedItem);
				}else{
					$this->handleFailure($transaction, $failed);
					continue;
				}
			}else{ //Normal inventory transaction
				//Quick hack for proof of concept. This will need fixing properly.
				$transaction->setSourceItem($transaction->getInventory()->getItem($transaction->getSlot()));
				
				$change = $transaction->getChange();

				if($change["out"] instanceof Item){
					if(($transaction->getInventory()->slotContains($transaction->getSlot(), $change["out"]) and $transaction->getInventory()->slotContains($transaction->getSlot(), $transaction->getSourceItem(), true)) or $this->player->isCreative()){
						//Allow adding nonexistent items to the crafting inventory in creative.

						$this->player->getCraftingInventory()->addItem($change["out"]);
						$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
						
						$transaction->setSuccess();
						$transaction->sendSlotUpdate($this->player);
					}else{
						$this->handleFailure($transaction, $failed);
						continue;
					}
				}
				if($change["in"] instanceof Item){
					if($this->player->getCraftingInventory()->contains($change["in"]) or $this->player->isCreative()){
						
						$this->player->getCraftingInventory()->removeItem($change["in"]);
						$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
						
						$transaction->setSuccess();
						$transaction->sendSlotUpdate($this->player);
					}else{
						$this->handleFailure($transaction, $failed);
						continue;
					}
				}
			}
			
			
			
		}
		$this->isExecuting = false;

		foreach($failed as $f){
			$f->sendSlotUpdate($this->player);
		}
		
		$this->lastExecution = microtime(true);
		$this->hasExecuted = true;

		return true;
	}
}