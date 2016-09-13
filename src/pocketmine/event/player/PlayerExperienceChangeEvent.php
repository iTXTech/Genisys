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
use pocketmine\entity\Human;

class PlayerExperienceChangeEvent extends PlayerEvent implements Cancellable{
	
	/** @deprecated */
	const ADD_EXPERIENCE = 0;
	const SET_EXPERIENCE = 1;
	
	public static $handlerList = null;
	
	public $progress;
	public $expLevel;

	public function __construct(Human $player, int $expLevel, float $progress){
		$this->progress = $progress;
		$this->expLevel = $expLevel;
		$this->player = $player;
	}
	
	/**
	 * @deprecated This is redundant, and will be removed in the future.
	 */
	public function getAction(){
		return self::SET_EXPERIENCE;
	}

	public function getExpLevel(){
		return $this->expLevel;
	}

	public function setExpLevel($level){
		$this->expLevel = $level;
	}

	public function getProgress(): float{
		return $this->progress;
	}
	
	public function setProgress(float $progress){
		$this->progress = $progress; //errors will be handled internally anyway
	}

	public function getExp(){
		return Human::getLevelXpRequirement($this->level) * $this->progress;
	}

	public function setExp($exp){
		$this->progress = $exp / Human::getLevelXpRequirement($this->level);
	}
}
