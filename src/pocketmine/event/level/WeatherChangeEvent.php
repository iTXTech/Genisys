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
	private $duration;

	public function __construct(Level $level, int $weather, int $duration){
		parent::__construct($level);
		$this->weather = $weather;
		$this->duration = $duration;
	}

	public function getWeather() : int{
		return $this->weather;
	}

	public function setWeather(int $weather = Weather::SUNNY){
		$this->weather = $weather;
	}

	public function getDuration() : int{
		return $this->duration;
	}

	public function setDuration(int $duration){
		$this->duration = $duration;
	}

}