<?php

namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\item\Item;

class OrderedTransactionGroup implements TransactionGroup{
	
	/** @var float */
	protected $creationTime;
	
	/** @var Player */
	protected $player = null;
	
	/** @var Transaction[] */
	protected $in = [];
	
	/** @var Transaction[] */
	protected $out = [];
	
	/** @var Inventory[] */
	protected $inventories;
	
	/** @var bool */
	protected $hasExecuted = false;
	
	/** @var float */
	protected $lastUpdate;
	
	/**
	 * @param Player $player
	 */
	public function __construct(Player $player = null){
		$this->player = $player;
		$this->creationTime = microtime(true);
	}
	
	public function getCreationTime(){
		return $this->creationTime;
	}
	
	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	/** 
	 * @return Transaction[]
	 *
	 * Returns an array of all waiting transactions.
	 */
	public function getTransactions(){
		return array_merge($this->out, $this->in);
	}
	
	/**
	 * @return Inventory[]
	 *
	 * Returns an array of all inventories involved in these transactions
	 */
	public function getInventories(){
		return $this->inventories;
	}
	
	/**
	 * @param Transaction $transaction
	 * @return bool
	 *
	 * Checks if a transaction is valid and what type of transaction it is,
	 * and then adds it to the relevant lists.
	 */
	public function addTransaction(Transaction $transaction){
		
		$change = $transaction->getChange();
		if($change === null){ //Ignore transactions where nothing happened.
			echo "invalid change supplied\n";
			return false;
		}
		if($transaction->getInventory() === null or !($transaction->getInventory() instanceof Inventory)){
			echo "invalid inventory supplied\n";
			return false;
		}
		
		$this->inventories[spl_object_hash($transaction->getInventory())] = $transaction->getInventory();
		
		if($change["in"] instanceof Item){
			echo "supplied an in item\n";
			$this->in[spl_object_hash($transaction)] = $transaction;
		}
		if($change["out"] instanceof Item){
			echo "supplied an out item\n";
			$this->out[spl_object_hash($transaction)] = $transaction;
		}
		
		$this->lastUpdate = microtime(true);
		return true;
	}
	
	/** @return bool */
	public function getLastUpdate(){
		return $this->lastUpdate;
	}
	
	/** @return bool 
	 *
	 * Checks that this will make:
	 * - if the transaction group hasn't had any new transactions added to it in the last quarter of a second (will need tuning)
	 * - AND if at least one of the lists contains a waiting transaction
	 */
	public function canExecute(){
		return ((microtime(true) - $this->getLastUpdate()) > 0.2) and (count($this->in) > 0 or count($this->out) > 0);
	}
	
	/** @return bool */
	public function hasExecuted(){
		return $this->hasExecuted;
	}
	
	/** @return bool
	 *
	 * Handles transaction execution
	 * All "out" transactions are handled before the "in" transactions.
	 */
	public function execute(){
		if(!$this->canExecute() or $this->hasExecuted()){
			echo "could not execute the transaction\n";
			return false;
		}
		echo "executing transaction\n";
			
		foreach($this->out as $transaction){
			$change = $transaction->getChange();
			if($transaction->getInventory()->slotContains($transaction->getSlot(), $change["out"]) or $this->player->isCreative()){
				//Potential for issues here, since all the "out" transactions are handled first
				// there is a potential for the crafting inventory to overflow, causing some
				// transactions to fail.
				// Will need fixing properly.
				echo "out transaction executing\n";
				//Do not send updates to the client until all transactions have been processed.
				//Once all transactions have been handled then use sendContents() to update the client-side.
				$this->player->getCraftingInventory()->addItem($change["out"]);
				$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem());

			}else{
				echo "out transaction failed\n";
				continue;
			}
		}
		foreach($this->in as $transaction){
			$change = $transaction->getChange();
			if($this->player->getCraftingInventory()->contains($change["in"]) or $this->player->isCreative()){
				echo "in transaction executing\n";
				$this->player->getCraftingInventory()->removeItem($change["in"]);
				$transaction->getInventory()->setItem($transaction->getSlot(), $transaction->getTargetItem());
			}else{
				//transaction failed
				echo "in transaction failed\n";
				continue;
			}
		}
		
		//Once all transactions are done, update the relevant viewers
		/*foreach($this->getInventories() as $inventory){
			$inventory->sendContents($inventory->getViewers());
		}*/
		
		$this->hasExecuted = true;
		
		return true;
	}
	
	
}
