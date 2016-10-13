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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Math;
use pocketmine\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\tile\Tile;
use pocketmine\tile\DLDetector;

class DaylightDetector extends Solid implements RedstoneSource{
	protected $id = self::DAYLIGHT_SENSOR;

	public function __construct(){
	}

	public function getName() : string{
		return "Daylight Sensor";
	}

	public function canBeActivated() : bool {
		return true;
	}

	/**
	 * @return DLDetector
	 */
	protected function getTile() : DLDetector{
		$t = $this->getLevel()->getTile($this);
		if($t instanceof DLDetector){
			return $t;
		}else{
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::DAY_LIGHT_DETECTOR),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			return Tile::createTile(Tile::DAY_LIGHT_DETECTOR, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}
	}

	public function onActivate(Item $item, Player $player = null){
		$this->id = $this->id == self::DAYLIGHT_SENSOR ? self::DAYLIGHT_SENSOR_INVERTED : self::DAYLIGHT_SENSOR;
		$this->getLevel()->setBlock($this, $this, false, true);
		return true;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		parent::place($item, $block, $target, $face, $fx, $fy, $fz, $player);

		if($this->getTile() instanceof DLDetector){
			return true;
		}

		return false;
	}

	public function getHardness() {
		return 3.0;
	}

	public function getResistance(){
		return 1;
	}

	public function getDrops(Item $item) : array {//>=Wood Axe
		return [
			[self::DAYLIGHT_SENSOR, 0, 1]
		];
	}

	public function getDirectRedstonePower(Block $block, int $face, int $powerMode) : int{
		return 0;
	}

	public function hasDirectRedstonePower(Block $block, int $face, int $powerMode) : bool{
		return false;
	}

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int{
		if(!$this->getLevel()->canBlockSeeSky($this)){
			return 0;
		}

		$i = 15 - $this->getLevel()->calculateSkylightSubtracted(1);
		$f = $this->getLevel()->getCelestialAngleRadians(1);

		if($this->id == self::DAYLIGHT_SENSOR_INVERTED){
			$i = 15 - $i;
		}

		if($i > 0 and $this->id == self::DAYLIGHT_SENSOR){
			$f1 = ($f < pi()) ? 0 : pi() * 2;
			$f = $f + ($f1 - $f) * 0.2;
			$i = round($i * cos($f));
		}

		return Math::clamp($i, 0 ,15);
	}
}