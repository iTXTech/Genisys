<?php
/**
 * Author: PeratX
 * OpenGenisys Project
 */
namespace pocketmine\level\weather;

use pocketmine\level\Level;

class WeatherManager{
	/** @var Level[] */
	public static $registeredLevel = [];
	
	public static function registerLevel(Level $level){
		self::$registeredLevel[$level->getName()] = $level;
		return true;
	}
	
	public static function unregisterLevel(Level $level){
		if(isset(self::$registeredLevel[$level->getName()])) {
			unset(self::$registeredLevel[$level->getName()]);
			return true;
		}
		return false;
	}
	
	public static function updateWeather(){
		foreach(self::$registeredLevel as $level) {
			$level->getWeather()->calcWeather();
		}
	}
	
	public static function isRegistered(Level $level){
		if(isset(self::$registeredLevel[$level->getName()])) return true;
		return false;
	}

}