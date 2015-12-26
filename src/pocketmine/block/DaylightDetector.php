<?php
/**
 * Author: PeratX
 * Time: 2015/12/24 0:20
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\weather\Weather;
use pocketmine\level\weather\WeatherManager;
use pocketmine\Player;

class DaylightDetector extends RedstoneSource{
	protected $id = self::DAYLIGHT_SENSOR;
	protected $hasStartedUpdate = false;

	public function getName(){
		return "Daylight Sensor";
	}

	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	public function canBeFlowedInto(){
		return false;
	}

	public function canBeActivated(){
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		$this->getLevel()->setBlock($this, new DaylightDetectorInverted(), true, true);
		return true;
	}

	public function getLightByTime(){
	/*	$strength = 1;
		$time = $this->getLevel()->getTime();
		if(WeatherManager::isRegistered($this->getLevel())) $weather = $this->getLevel()->getWeather()->getWeather();
		else $weather = Weather::SUNNY;
		switch($weather){
			case Weather::SUNNY:
				if($time <= 22340 and $time >= 13680) $strength = 1;
				if($time <= 22800 and $time >= 13220) $strength = 2;
				if($time <= 23080 and $time >= 12940) $strength = 3;
				if($time <= 23300 and $time >= 12720) $strength = 4;
				if($time <= 23540 and $time >= 12480) $strength = 5;
				if($time <= 23780 and $time >= 12240) $strength = 6;
				if($time <= 23960 and $time >= 12040) $strength = 7;
				if($time >= 180 and $time <= 11840) $strength = 8;
				if($time >= 540 and $time <= 11480) $strength = 9;
				if($time >= 940 and $time <= 11080) $strength = 10;
				if($time >= 1380 and $time <= 10640) $strength = 11;
				if($time >= 1880 and $time <= 10140) $strength = 12;
				if($time >= 2460 and $time <= 9560) $strength = 13;
				if($time >= 3180 and $time <= 8840) $strength = 14;
				if($time >= 4300 and $time <= 7720) $strength = 15;
				break;
			case Weather::RAINY_THUNDER:
			case Weather::RAINY:
				if($time <= 22340 and $time >= 13680) $strength = 1;
				if($time <= 22800 and $time >= 13220) $strength = 2;
				if($time <= 23240 and $time >= 12780) $strength = 3;
				if($time <= 23520 and $time >= 12500) $strength = 4;
				if($time <= 23760 and $time >= 12260) $strength = 5;
				if($time >= 0 and $time <= 12020) $strength = 6;
				if($time >= 400 and $time <= 11620) $strength = 7;
				if($time >= 900 and $time <= 11120) $strength = 8;
				if($time >= 1440 and $time <= 10580) $strength = 9;
				if($time >= 2080 and $time <= 9940) $strength = 10;
				if($time >= 2880 and $time <= 9140) $strength = 11;
				if($time >= 4120 and $time <= 7990) $strength = 12;
				break;
		}
		return $strength;*/
		$time = $this->getLevel()->getTime();
		if(($time >= Level::TIME_DAY and $time <= Level::TIME_SUNSET) or
				($time >= Level::TIME_SUNRISE and $time <= Level::TIME_FULL)) return 15;
		return 0;
	}

	public function isActivated(){
		if(!$this->hasStartedUpdate) $this->onUpdate(Level::BLOCK_UPDATE_NORMAL);
		return ($this->getLightByTime() > 0);
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL or $type == Level::BLOCK_UPDATE_SCHEDULED){
			$this->hasStartedUpdate = true;
			if($this->getLightByTime() > 0) $this->activate();
			else $this->deactivate();
			$this->getLevel()->scheduleUpdate($this, $this->getLevel()->getServer()->getTicksPerSecondAverage() * 3);
			return Level::BLOCK_UPDATE_NORMAL;
		}
		return true;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air());
		if($this->isActivated()) $this->deactivate();
	}

	public function getDrops(Item $item){
		return [
			[self::DAYLIGHT_SENSOR, 0, 1]
		];
	}
}