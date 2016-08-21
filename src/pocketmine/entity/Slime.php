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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Slime extends Monster{
	const NETWORK_ID = self::SLIME;

	const DATA_SLIME_SIZE = 16; //byte
	
	const SIZE_TINY = 1;
	const SIZE_MEDIUM = 2;
	const SIZE_BIG = 4;

	public $width = 0.51;
	public $length = 0.51;
	public $height = 0.51; //Defaults for small slime

	public $dropExp = [1, 4];
	
	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt["Size"])){
			$nbt->Size = new IntTag("Size", self::getRandomSize());
		}
		parent::__construct($chunk, $nbt);
	}
	
	public function initEntity(){
		if(!isset($this->namedtag["Size"])){
			$this->namedtag->Size = new IntTag("Size", self::getRandomSize());
		}
		$this->setDataProperty(self::DATA_SLIME_SIZE, self::DATA_TYPE_BYTE, $this->namedtag["Size"]);
		$this->width = $this->length = $this->height = 0.51 * $this->getSize();
		$this->setHealth($this->getSize() ** 2);
		$this->setMaxHealth($this->getSize() ** 2);
		parent::initEntity();
	}
	
	public function getName() : string{
		return "Slime";
	}

	public function getSize(): int{
		return $this->namedtag["Size"];
	}
	
	public function setSize(int $size){
		$this->namedtag["Size"] = $size & 0xff; //255 is the biggest possible size
		$this->setDataProperty(self::DATA_SLIME_SIZE, self::DATA_TYPE_BYTE, $size);
	}
	
	public static function getRandomSize(): int{
		$rand = mt_rand(0, 100);
		if($rand <= 17){
			return self::SIZE_TINY;
		}elseif($rand <= 50){
			return self::SIZE_MEDIUM;
		}else{
			return self::SIZE_BIG;
		}
	}
	
	public function kill(){
		parent::kill();
		if(($cause = $this->lastDamageCause) instanceof EntityDamageByEntityEvent and ($player = $cause->getDamager()) instanceof Player and $this->getSize() > self::SIZE_TINY){
			$randomAmount = mt_rand(2, 4);
			for($i = 1; $i <= $randomAmount; ++$i){
				$nbt = clone $this->namedtag;
				$nbt["Size"] = new IntTag("Size", floor($this->getSize() / 2));
				$nbt["Pos"] = new ListTag("Pos", [
					new DoubleTag("", $this->x + ($i & 0x01 === 1 ? 0.5: -0.5)),
					new DoubleTag("", $this->y),
					new DoubleTag("", $this->z + ($i & 0x01 === 0 ? 0.5: -0.5))
				]);
				$nbt["Rotation"]  = new ListTag("Rotation", [ //Spawn facing player
					new FloatTag("", ($player->getYaw() < 180 ? $player->getYaw() + 180: $player->getYaw() - 180)), //To spawn facing the player.
					new FloatTag("", 0)
				]);
				$new = Entity::createEntity(static::NETWORK_ID, $this->chunk, $nbt); //To allow this to be used for magma cubes also
				$new->spawnToAll();
			}
		}
	}
	
	public function getDrops(){
		if($this->getSize() === self::SIZE_TINY){
			return [
				ItemItem::get(ItemItem::SLIMEBALL, 0, mt_rand(0, 2))
			];
		}
		return [];
	}
}