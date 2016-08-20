<?php

/*
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
 * @link http://www.pocketmine.net/
 * 
 *
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\IntTag;

abstract class Animal extends Creature implements Ageable{

	public function initEntity(){
		parent::initEntity();
		if(!isset($this->namedtag["Age"])){
			$this->namedtag->Age = new IntTag("Age", self::getRandomAge());
		}
		$this->setDataProperty(self::DATA_AGEABLE_FLAGS, self::DATA_TYPE_BYTE, $this->isBaby());
	}

	public function entityBaseTick($tickDiff = 1){
		$stateChange = false;
		if($this->isBaby()){
			$this->namedtag["Age"] += $tickDiff; //Minimum value of -24000
			$stateChange = true;
		}
		//TODO: Breeding time
		
		if($this->namedtag["Age"] === 0 and $stateChange){
			$this->setDataProperty(self::DATA_AGEABLE_FLAGS, self::DATA_TYPE_BYTE, self::DATA_FLAG_ADULT);
		}
		
		return parent::entityBaseTick($tickDiff);
	}
	
	protected static function getRandomAge(): int{
		$chance = mt_rand(0, 100);
		if($chance < 5){
			//Since the wiki has no data for this, I'll go with 5% of mobs from eggs being babies.
			return mt_rand(-24000, -1);
		}
		return 0;
	}

	public function isBaby(){
		return $this->namedtag["Age"] < 0; //Less than zero is baby. Zero is adult. Positive is time until mob can breed again.
	}
}