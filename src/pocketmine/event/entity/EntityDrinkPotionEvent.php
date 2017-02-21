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

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Potion;

class EntityDrinkPotionEvent extends EntityEvent implements Cancellable{
	
	public static $handlerList = null;
	
	/* @var Potion */
	private $potion;
	
	/* @var Effect[] */
	private $effects;
	
	public function __construct(Entity $entity, Potion $potion){
		$this->entity = $entity;
		$this->potion = $potion;
		$this->effects = $potion->getEffects();
	}
	
	public function getEffects(){
		return $this->effects;
	}
	
	public function getPotion(){
		return $this->potion;
	}
}
