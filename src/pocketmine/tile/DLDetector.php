<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/5 15:10


 *
 * OpenGenisys Project
 */
namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\block\DaylightDetector;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\level\Level;

class DLDetector extends Spawnable{
	private $lastType = 0;

	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		parent::__construct($chunk, $nbt);
		$this->scheduleUpdate();
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

	public function isActivated() : bool{
		if($this->getType() == Block::DAYLIGHT_SENSOR) {
			if($this->getLightByTime() == 15) return true;
			return false;
		}else{
			if($this->getLightByTime() == 0) return true;
			return false;
		}
	}

	private function getType() : int{
		return $this->getBlock()->getId();
	}

	public function onUpdate(){
		if(($this->getLevel()->getServer()->getTick() % 3) == 0){ //Update per 3 ticks
			if($this->getType() != $this->lastType){ //Update when changed
				/** @var DaylightDetector $block */
				$block = $this->getBlock();
				if($this->isActivated()){
					$block->activate();
				}else{
					$block->deactivate();
				}
				$this->lastType = $block->getId();
			}
		}
		return true;
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::DAY_LIGHT_DETECTOR),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
		]);
	}
}