<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\item\Item;

class EntityDamageEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const MODIFIER_BASE = 0;
	const MODIFIER_RESISTANCE = 1;
	const MODIFIER_ARMOR = 2;
	const MODIFIER_PROTECTION = 3;
	const MODIFIER_STRENGTH = 4;
	const MODIFIER_WEAKNESS = 5;


	const CAUSE_CONTACT = 0;
	const CAUSE_ENTITY_ATTACK = 1;
	const CAUSE_PROJECTILE = 2;
	const CAUSE_SUFFOCATION = 3;
	const CAUSE_FALL = 4;
	const CAUSE_FIRE = 5;
	const CAUSE_FIRE_TICK = 6;
	const CAUSE_LAVA = 7;
	const CAUSE_DROWNING = 8;
	const CAUSE_BLOCK_EXPLOSION = 9;
	const CAUSE_ENTITY_EXPLOSION = 10;
	const CAUSE_VOID = 11;
	const CAUSE_SUICIDE = 12;
	const CAUSE_MAGIC = 13;
	const CAUSE_CUSTOM = 14;
	const CAUSE_STARVATION = 15;

	const CAUSE_LIGHTNING = 16;


	private $cause;
	/** @var array */
	private $modifiers;
	private $originals;
	private $use_armors = [];


	/**
	 * @param Entity    $entity
	 * @param int       $cause
	 * @param int|int[] $damage
	 *
	 * @throws \Exception
	 */
	public function __construct(Entity $entity, $cause, $damage){
		$this->entity = $entity;
		$this->cause = $cause;
		if(is_array($damage)){
			$this->modifiers = $damage;
		}else{
			$this->modifiers = [
				self::MODIFIER_BASE => $damage
			];
		}

		$this->originals = $this->modifiers;

		if(!isset($this->modifiers[self::MODIFIER_BASE])){
			throw new \InvalidArgumentException("BASE Damage modifier missing");
		}

		//For DAMAGE_RESISTANCE
		if($cause !== self::CAUSE_VOID and $cause !== self::CAUSE_SUICIDE){
			if($entity->hasEffect(Effect::DAMAGE_RESISTANCE)){
				$RES_level = 1 - 0.20 * ($entity->getEffect(Effect::DAMAGE_RESISTANCE)->getAmplifier() + 1);
				if($RES_level < 0){
					$RES_level = 0;
				}
				$this->setDamage($RES_level, self::MODIFIER_RESISTANCE);
			}
		}

		//For MODIFIER_ARMOR
		switch($cause){
			case self::CAUSE_CONTACT:
			case self::CAUSE_ENTITY_ATTACK:
			case self::CAUSE_PROJECTILE:
			case self::CAUSE_FIRE:
			case self::CAUSE_LAVA:
			case self::CAUSE_BLOCK_EXPLOSION:
			case self::CAUSE_ENTITY_EXPLOSION:
			case self::CAUSE_LIGHTNING:
				if($entity instanceof Player){
					$points = 0;
					foreach($entity->getInventory()->getArmorContents() as  $i){
						if($i->isArmor()){
							$points += $i->getArmorValue();
						}
					}
					if($points !== 0){
						$this->setDamage(1 - 0.04 * $points, self::MODIFIER_ARMOR);
						$this->use_armors = $entity->getInventory()->getArmorContents();
					}
				}
				break;
			case self::CAUSE_FALL:
				break;
			case self::CAUSE_FIRE_TICK:
				break;
			case self::CAUSE_SUFFOCATION:
			case self::CAUSE_DROWNING:
			case self::CAUSE_VOID:
			case self::CAUSE_SUICIDE:
			case self::CAUSE_MAGIC:
			case self::CAUSE_CUSTOM:
			case self::CAUSE_STARVATION:
				break;
			default:
				break;
		}

		//For MODIFIER_PROTECTION TODO: add all kind of PROTECTION Enchantment
	}

	/**
	 * @return int
	 */
	public function getCause(){
		return $this->cause;
	}

	/**
	 * @param int $type
	 *
	 * @return int
	 */
	public function getOriginalDamage($type = self::MODIFIER_BASE){
		if(isset($this->originals[$type])){
			return $this->originals[$type];
		}

		return 0;
	}

	/**
	 * @param int $type
	 *
	 * @return int
	 */
	public function getDamage($type = self::MODIFIER_BASE){
		if(isset($this->modifiers[$type])){
			return $this->modifiers[$type];
		}

		return 0;
	}

	/**
	 * @param float $damage
	 * @param int   $type
	 *
	 * @throws \UnexpectedValueException
	 */
	public function setDamage($damage, $type = self::MODIFIER_BASE){
		$this->modifiers[$type] = $damage;
	}

	/**
	 * @param int $type
	 *
	 * @return bool
	 */
	public function isApplicable($type){
		return isset($this->modifiers[$type]);
	}

	/**
	 * @return int
	 */
	public function getFinalDamage(){
		$damage = 1;
		foreach($this->modifiers as $type => $d){
			$damage *= $d;
		}
		return $damage;
	}

	/**
	 * @return Item $use_armors
	 */
	public function getUsedArmors(){
		return $this->use_armors;
	}

	/**
	 * @return bool
	 */
	public function useArmors(){
		if($this->entity instanceof Player){
			if($this->entity->isSurvival() and $this->entity->isAlive()){
				foreach ($this->use_armors as $index=>$i){
					if($i->isArmor()){
						$i->useOn($i);
						$this->entity->getInventory()->setArmorItem($index, $i);
					}
				}
			}
			return true;
		}
		return false;
	}
}
