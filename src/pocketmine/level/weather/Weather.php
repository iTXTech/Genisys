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
 * @link https://mcper.cn
 *
 */

namespace pocketmine\level\weather;

use pocketmine\event\level\WeatherChangeEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Player;

class Weather{
	const CLEAR = 0;
	const SUNNY = 0;
	const RAIN = 1;
	const RAINY = 1;
	const RAINY_THUNDER = 2;
	const THUNDER = 3;

	private $level;
	private $weatherNow = 0;
	private $strength1;
	private $strength2;
	private $duration;
	private $canCalculate = true;

	/** @var Vector3 */
	private $temporalVector = null;

	private $lastUpdate = 0;

	private $randomWeatherData = [0, 1, 0, 1, 0, 1, 0, 2, 0, 3];

	public function __construct(Level $level, $duration = 1200){
		$this->level = $level;
		$this->weatherNow = self::SUNNY;
		$this->duration = $duration;
		$this->lastUpdate = $level->getServer()->getTick();
		$this->temporalVector = new Vector3(0, 0, 0);
	}

	public function canCalculate() : bool{
		return $this->canCalculate;
	}

	public function setCanCalculate(bool $canCalc){
		$this->canCalculate = $canCalc;
	}

	public function calcWeather($currentTick){
		if($this->canCalculate()){
			$tickDiff = $currentTick - $this->lastUpdate;
			$this->duration -= $tickDiff;
			if($this->duration <= 0){
				//0晴天1下雨2雷雨3阴天雷
				if($this->weatherNow == self::SUNNY){
					$weather = $this->randomWeatherData[array_rand($this->randomWeatherData)];
					$duration = mt_rand(min($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax), max($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax));;
					$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, $weather, $duration));
					if(!$ev->isCancelled()){
						$this->weatherNow = $ev->getWeather();
						$this->strength1 = mt_rand(90000, 110000);
						$this->strength2 = mt_rand(30000, 40000);
						$this->duration = $ev->getDuration();
						$this->changeWeather($this->weatherNow, $this->strength1, $this->strength2);
					}
				}else{
					$weather = self::SUNNY;
					$duration = mt_rand(min($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax), max($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax));
					$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, $weather, $duration));
					if(!$ev->isCancelled()){
						$this->weatherNow = $ev->getWeather();
						$this->strength1 = 0;
						$this->strength2 = 0;
						$this->duration = $ev->getDuration();
						$this->changeWeather($this->weatherNow, $this->strength1, $this->strength2);
					}
				}
			}
			if(($this->weatherNow > 0) and ($this->level->getServer()->lightningTime > 0) and is_int($this->duration / $this->level->getServer()->lightningTime)){
				$players = $this->level->getPlayers();
				if(count($players) > 0){
					$p = $players[array_rand($players)];
					$x = $p->x + mt_rand(-64, 64);
					$z = $p->z + mt_rand(-64, 64);
					$y = $this->level->getHighestBlockAt($x, $z);
					$this->level->spawnLightning($this->temporalVector->setComponents($x, $y, $z));
				}
				/*foreach($this->level->getPlayers() as $p){
					if(mt_rand(0, 1) == 1){
						$x = $p->getX() + rand(-100, 100);
						$y = $p->getY() + rand(20, 50);
						$z = $p->getZ() + rand(-100, 100);
						$this->level->sendLighting($x, $y, $z, $p);
					}
				}*/
			}
		}
		$this->lastUpdate = $currentTick;
	}

	public function setWeather(int $wea, int $duration = 12000){
		$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, $wea, $duration));
		if(!$ev->isCancelled()){
			$this->weatherNow = $ev->getWeather();;
			$this->strength1 = mt_rand(90000, 110000);
			$this->strength2 = mt_rand(30000, 40000);
			$this->duration = $ev->getDuration();
			$this->changeWeather($wea, $this->strength1, $this->strength2);
		}
	}

	public function getRandomWeatherData() : array{
		return $this->randomWeatherData;
	}

	public function setRandomWeatherData(array $randomWeatherData){
		$this->randomWeatherData = $randomWeatherData;
	}

	public function getWeather() : int{
		return $this->weatherNow;
	}

	public static function getWeatherFromString($weather){
		if(is_int($weather)){
			if($weather <= 3){
				return $weather;
			}
			return self::SUNNY;
		}
		switch(strtolower($weather)){
			case "clear":
			case "sunny":
			case "fine":
				return self::SUNNY;
			case "rain":
			case "rainy":
				return self::RAINY;
			case "thunder":
				return self::THUNDER;
			case "rain_thunder":
			case "rainy_thunder":
				return self::RAINY_THUNDER;
			default:
				return self::SUNNY;
		}
	}

	/**
	 * @return bool
	 */
	public function isSunny() : bool{
		if($this->getWeather() == self::SUNNY){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isRainy() : bool{
		if($this->getWeather() == self::RAINY){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isRainyThunder() : bool{
		if($this->getWeather() == self::RAINY_THUNDER){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isThunder() : bool{
		if($this->getWeather() == self::THUNDER){
			return true;
		}else{
			return false;
		}
	}

	public function getStrength() : array{
		return [$this->strength1, $this->strength2];
	}

	public function sendWeather(Player $p){
		$pk1 = new LevelEventPacket;
		$pk1->evid = LevelEventPacket::EVENT_STOP_RAIN;
		$pk1->data = $this->strength1;
		$pk2 = new LevelEventPacket;
		$pk2->evid = LevelEventPacket::EVENT_STOP_THUNDER;
		$pk2->data = $this->strength2;
		if($p->weatherData[0] != $this->weatherNow){
			$p->dataPacket($pk1);
			$p->dataPacket($pk2);
			if($this->weatherNow == 1){
				$pk = new LevelEventPacket;
				$pk->evid = LevelEventPacket::EVENT_START_RAIN;
				$pk->data = $this->strength1;
				$p->dataPacket($pk);
			}elseif($this->weatherNow == 2){
				$pk = new LevelEventPacket;
				$pk->evid = LevelEventPacket::EVENT_START_RAIN;
				$pk->data = $this->strength1;
				$p->dataPacket($pk);
				$pk = new LevelEventPacket;
				$pk->evid = LevelEventPacket::EVENT_START_THUNDER;
				$pk->data = $this->strength2;
				$p->dataPacket($pk);
			}elseif($this->weatherNow == 3){
				$pk = new LevelEventPacket;
				$pk->evid = LevelEventPacket::EVENT_START_THUNDER;
				$pk->data = $this->strength2;
				$p->dataPacket($pk);
			}
			$p->weatherData = [$this->weatherNow, $this->strength1, $this->strength2];
		}
	}

	public function changeWeather(int $wea, int $strength1, int $strength2){
		$pk1 = new LevelEventPacket;
		$pk1->evid = LevelEventPacket::EVENT_STOP_RAIN;
		$pk1->data = $this->strength1;
		$pk2 = new LevelEventPacket;
		$pk2->evid = LevelEventPacket::EVENT_STOP_THUNDER;
		$pk2->data = $this->strength2;
		foreach($this->level->getPlayers() as $p){
			if($p->weatherData[0] != $wea){
				$p->dataPacket($pk1);
				$p->dataPacket($pk2);
				if($wea == 1){
					$pk = new LevelEventPacket;
					$pk->evid = LevelEventPacket::EVENT_START_RAIN;
					$pk->data = $strength1;
					$p->dataPacket($pk);
				}elseif($wea == 2){
					$pk = new LevelEventPacket;
					$pk->evid = LevelEventPacket::EVENT_START_RAIN;
					$pk->data = $strength1;
					$p->dataPacket($pk);
					$pk = new LevelEventPacket;
					$pk->evid = LevelEventPacket::EVENT_START_THUNDER;
					$pk->data = $strength2;
					$p->dataPacket($pk);
				}elseif($wea == 3){
					$pk = new LevelEventPacket;
					$pk->evid = LevelEventPacket::EVENT_START_THUNDER;
					$pk->data = $strength2;
					$p->dataPacket($pk);
				}
				$p->weatherData = [$wea, $strength1, $strength2];
			}
		}
	}

}
	