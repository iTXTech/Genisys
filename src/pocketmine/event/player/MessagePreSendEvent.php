<?php



namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Called when a player is sent to some messages by using SendMessage()
 */
class MessagePreSendEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var string */
	protected $message;

	public function __construct(Player $player, $message){
		$this->player = $player;
		$this->message = $message;
	}

	public function getMessage(){
		return $this->message;
	}

	public function setMessage($message){
		$this->message = $message;
	}

}