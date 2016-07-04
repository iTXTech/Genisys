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
		$this->player = $player;
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
