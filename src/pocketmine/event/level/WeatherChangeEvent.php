<?php
/**
 * Author: PeratX
 * Time: 2015/12/27 18:01
 ]

 *
 * OpenGenisys Project
 */
namespace pocketmine\event\level;

use pocketmine\event\Cancellable;
use pocketmine\level\Level;
use pocketmine\level\weather\Weather;

class WeatherChangeEvent extends LevelEvent implements Cancellable{
	public static $handlerList = null;

	private $weather;

	public function __construct(Level $level, $weather){
		parent::__construct($level);
		$this->weather = $weather;
	}

	public function getWeather(){
		return $this->weather;
	}

	public function setWeather($weather = Weather::SUNNY){
		$this->weather = $weather;
	}

}