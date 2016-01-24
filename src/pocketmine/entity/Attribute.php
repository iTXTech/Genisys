<?php

/*
 *
 *  ____			_		_   __  __ _				  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_|
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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\network\Network;
use pocketmine\network\protocol\MobEffectPacket;
use pocketmine\network\protocol\UpdateAttributePacket;
use pocketmine\Player;
use pocketmine\entity\AttributeManager;

class Attribute{
	const MAX_HEALTH = 0;
	const MAX_HUNGER = 1;

	const EXPERIENCE = 2;
	const EXPERIENCE_LEVEL = 3;

	private $id;
	protected $minValue;
	protected $maxValue;
	protected $defaultValue;
	protected $currentValue;
	protected $name;
	protected $shouldSend;

	/** @var Player */
	protected $player;
	
	protected static $attributes = [];
	
	public static function init(){
		self::addAttribute(AttributeManager::MAX_HEALTH, "generic.health", 0, 20, 20, true);
		self::addAttribute(AttributeManager::MAX_HUNGER, "player.hunger", 0, 20, 20, true);
		self::addAttribute(AttributeManager::EXPERIENCE, "player.experience", 0, 1, 0, true);
		self::addAttribute(AttributeManager::EXPERIENCE_LEVEL, "player.level", 0, 24791, 0, true);
	}
	
	public static function getAttribute($id){
		return isset(self::$attributes[$id]) ? clone self::$attributes[$id] : null;
	}
	
	public static function addAttribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend = false){
		if($minValue > $maxValue or $defaultValue > $maxValue or $defaultValue < $minValue){
			throw new \InvalidArgumentException("Invalid ranges: min value: $minValue, max value: $maxValue, $defaultValue: $defaultValue");
		}
		return self::$attributes[(int) $id] = new Attribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend);
	}
	/**
	 * @param $name
	 * @return null|Attribute
	 */
	public static function getAttributeByName($name){
		foreach(self::$attributes as $a){
			if($a->getName() === $name){
				return clone $a;
			}
		}
		
		return null;
	}

	public function __construct($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend, $player = null){
		$this->id = (int) $id;
		$this->name = (string) $name;
		$this->minValue = (float) $minValue;
		$this->maxValue = (float) $maxValue;
		$this->defaultValue = (float) $defaultValue;
		$this->shouldSend = (float) $shouldSend;

		$this->currentValue = $this->defaultValue;

		$this->player = $player;
	}

	public function getMinValue(){
		return $this->minValue;
	}

	public function setMinValue($minValue){
		if($minValue > $this->getMaxValue()){
			throw new \InvalidArgumentException("Value $minValue is bigger than the maxValue!");
		}

		$this->minValue = $minValue;
		return $this;
	}

	public function getMaxValue(){
		return $this->maxValue;
	}

	public function setMaxValue($maxValue){
		if($maxValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Value $maxValue is smaller than the minValue!");
		}

		$this->maxValue = $maxValue;
		return $this;
	}

	public function getDefaultValue(){
		return $this->defaultValue;
	}

	public function setDefaultValue($defaultValue){
		if($defaultValue > $this->getMaxValue() or $defaultValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Value $defaultValue exceeds the range!");
		}

		$this->defaultValue = $defaultValue;
		return $this;
	}

	public function getValue(){
		return $this->currentValue;
	}

	public function setValue($value){
		if($value > $this->getMaxValue()){
			$value = $this->getMaxValue();
		} else if($value < $this->getMinValue()){
			$value = $this->getMinValue();
		}
		$this->currentValue = $value;

		if($this->shouldSend and $this->player != null)
			$this->send();
	}

	public function getName() : string{
		return $this->name;
	}

	public function getId(){
		return $this->id;
	}

	public function isSyncable(){
		return $this->shouldSend;
	}

	public function send() {
		$pk = new UpdateAttributePacket();
		$pk->maxValue = $this->getMaxValue();
		$pk->minValue = $this->getMinValue();
		$pk->value = $this->currentValue;
		$pk->name = $this->getName();
		$pk->entityId = 0;
		$pk->encode();
		$this->player->dataPacket($pk);
	}
}