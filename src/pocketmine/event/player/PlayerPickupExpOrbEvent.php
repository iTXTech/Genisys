<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/9 13:17


 *
 * OpenGenisys Project
 */
namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerPickupExpOrbEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	private $amount;

	public function __construct(Player $p, int $amount = 0){
		$this->player = $p;
		$this->amount = $amount;
	}

	public function getAmount() : int{
		return $this->amount;
	}

	public function setAmount(int $amount){
		$this->amount = $amount;
	}
}