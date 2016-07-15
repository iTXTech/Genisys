<?php

/*
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
use pocketmine\item\Item;

class SimpleTransactionQueue implements TransactionQueue{
	
	const ALLOWED_RETRIES = 20;
	
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
	public function getInventories(){
		return $this->inventories;
	}
	
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
		$change = $transaction->getChange();
		
		if(@$change["in"] instanceof Item or @$change["out"] instanceof Item){
			$this->transactionQueue->enqueue($transaction);
			$this->inventories[] = $transaction->getInventory();
			$this->lastUpdate = microtime(true);
			return true;
		}else{
			return false;
		}
	}
	
	
	/** 
	 * @param Transaction 	$transaction
	 * @param Transaction[] &$completed
	 *
	 * Handles a failed transaction
	 */
	private function handleFailure(Transaction $transaction, &$failed){
		$transaction->addFailure();
		if($transaction->getFailures() > self::ALLOWED_RETRIES){
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
		}else*/if(microtime(true) - $this->lastUpdate < 0.5){
			echo "last update time less than 10 ticks ago\n";
			return false;
		}
		//echo "Starting queue execution\n";
		
		/** @var Transaction[] */
		$failed = [];
		
		$this->isExecuting = true;
		
		while(!$this->transactionsToRetry->isEmpty()){
			//Some failed transactions are waiting from the previous execution to be retried
			echo "adding a transaction to retry\n";
			$this->transactionQueue->enqueue($this->transactionsToRetry->dequeue());
		}
		
		while(!$this->transactionQueue->isEmpty()){
			$transaction = $this->transactionQueue->dequeue();
			
			//Quick hack for proof of concept. This will need fixing properly.
			$transaction->setSourceItem($transaction->getInventory()->getItem($transaction->getSlot()));
			
			$change = $transaction->getChange();
			//var_dump($change);
			if($change["out"] instanceof Item){
				if(($transaction->getInventory()->slotContains($transaction->getSlot(), $change["out"]) and $transaction->getInventory()->slotContains($transaction->getSlot(), $transaction->getSourceItem(), true)) or $this->player->isCreative()){
					//Allow adding nonexistent items to the crafting inventory in creative.
					echo "out transaction executing\n";

					$this->player->getCraftingInventory()->addItem($change["out"]);
					$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
					
					$transaction->setSuccess();
					$transaction->sendSlotUpdate($this->player);
				}else{
					//Transaction unsuccessful
					echo "out transaction failed\n";
					//$transaction->addFailure();
					//$failed[] = $transaction;
					//Relocate the transaction to the end of the list
					/*$transaction->addFailure();
					if($transaction->getFailures() > 2){
						$failed[] = $transaction;
					}else{
						//Add the transaction to the back of the queue to be retried
						$this->transactionQueue->enqueue($transaction);
					}*/
					$this->handleFailure($transaction, $failed);
					continue;
				}
			}
			if($change["in"] instanceof Item){
				if($this->player->getCraftingInventory()->contains($change["in"]) or $this->player->isCreative()){
					echo "in transaction executing\n";
					
					$this->player->getCraftingInventory()->removeItem($change["in"]);
					$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem(), false);
					
					$transaction->setSuccess();
					$transaction->sendSlotUpdate($this->player);
				}else{
					//Transaction unsuccessful
					echo "in transaction failed\n";
					/*$transaction->addFailure();
					$failed[] = $transaction;
					//Relocate the transaction to the end of the list
					$transaction->addFailure();
					if($transaction->getFailures() > 2){
						$failed[] = $transaction;
					}else{
						//Add the transaction to the back of the queue to be retried
						$this->transactionQueue->enqueue($transaction);
					}*/
					$this->handleFailure($transaction, $failed);
					continue;
				}
			}
		}
		$this->isExecuting = false;
		//echo "Finished queue execution\n";
		//$this->transactionQueue = null;
		foreach($failed as $f){
			//No, no no. Only send failed transaction updates to the _instigator_.
			//Send successful transaction updates to _viewers_
			$f->sendSlotUpdate($this->player);
		}
		/*foreach($this->inventories as $inventory){
			$inventory->sendContents($inventory->getViewers());
		}*/
		
		//$this->inventories = [];
		$this->lastExecution = microtime(true);
		$this->hasExecuted = true;

		return true;
	}
}