<?php
// -	 -   -------    /----
// |     |      |      |
// |	 |      |       \----\
// |     |      |             |
//  \___/       |       _____/
// This file is created by Zzm and modified by PeratX
namespace pocketmine\level\weather;

use pocketmine\event\level\WeatherChangeEvent;
use pocketmine\level\Level;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Player;

class Weather{
	const SUNNY = 0;
	const RAINY = 1;
	const RAINY_THUNDER = 2;
	const THUNDER = 3;

	public $level;
	public $weatherTime = 0;
	public $weatherNow = 0;
	public $weatherLast = 0;
	public $strength1;
	public $strength2;
	public $wea = [0, 1, 0, 1, 0, 1, 0, 2, 0, 3];

	public function __construct(Level $level, $weatherTime = 12000, $weatherNow = 0){
		$this->level = $level;
		$this->weatherTime = $weatherTime;
		$this->weatherNow = $weatherNow;
		$this->weatherLast = 0;
		WeatherManager::registerLevel($level);
	}

	public function calcWeather(){
		$this->weatherTime++;
		$this->weatherLast++;
		if($this->weatherTime >= $this->level->getServer()->weatherChangeTime){
			$this->weatherTime = 0;
			//0晴天1下雨2雷雨3阴天雷
			$weather = $this->wea[mt_rand(0, count($this->wea) - 1)];
			$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, $weather));
			if($ev->isCancelled()){
				return;
			}else{
				$this->weatherNow = $ev->getWeather();
				$this->strength1 = mt_rand(90000, 110000);
				$this->strength2 = mt_rand(30000, 40000);
				$this->changeWeather($this->weatherNow, $this->strength1, $this->strength2);
			}
		}
		if($this->weatherLast >= $this->level->getServer()->weatherLastTime and $this->level->getServer()->weatherLastTime > 0){
			$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, 0));
			if($ev->isCancelled()){
				return;
			}else{
				$this->weatherNow = $ev->getWeather();
				$this->strength1 = 0;
				$this->strength2 = 0;
				$this->changeWeather($this->weatherNow, $this->strength1, $this->strength2);
			}
		}
		if(($this->weatherNow > 0) and is_int($this->weatherTime / $this->level->getServer()->lightningTime)){
			foreach($this->level->getPlayers() as $p){
				$x = $p->getX() + rand(-100, 100);
				$y = $p->getY() + rand(20, 50);
				$z = $p->getZ() + rand(-100, 100);
				$this->level->addwLighting($x, $y, $z, $p);
			}
		}
	}

	public function setWeather($wea){
		$this->weatherTime = 0;
		$this->weatherNow = $wea;
		$this->strength1 = mt_rand(90000, 110000);
		$this->strength2 = mt_rand(30000, 40000);
		$this->changeWeather($wea, $this->strength1, $this->strength2);
	}

	public function getWeather() : int{
		return $this->weatherNow;
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

	public function changeWeather($wea, $strength1, $strength2){
		$this->weatherLast = 0;
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
	