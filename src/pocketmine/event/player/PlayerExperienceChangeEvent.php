<?php
namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerExperienceChangeEvent extends PlayerEvent implements Cancellable{
	const ADD_EXPERIENCE = 0;
	const SET_EXPERIENCE = 1;
	
	public static $handlerList = null;
	
	public $exp;
	public $expLevel;
	public $action;

	public function __construct(Player $player, $exp, $expLevel, $action = PlayerExperienceChangeEvent::SET_EXPERIENCE){
		$this->exp = $exp;
		$this->expLevel = $expLevel;
		$this->action = $action;
	}
	
	public function getAction(){
		return $this->action;
	}
	
	public function getExp(){
		return $this->exp;
	}
	
	public function getExpLevel(){
		return $this->expLevel;
	}
	
	public function setExp($exp){
		$this->exp = $exp;
	}
	
	public function setExpLevel($level){
		$this->expLevel = $level;
	}

}
