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
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\block\Wool;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\format\Chunk;
use pocketmine\nbt\tag\CompoundTag;

class Sheep extends Animal implements Colorable{
	const NETWORK_ID = 13;

	const DATA_COLOR_INFO = 16;

	public $width = 0.625;
	public $length = 1.4375;
	public $height = 1.8;
	
	public function getName() : string{
		return "Sheep";
	}

	public function __construct(Chunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Color)){
			$nbt->Color = new ByteTag("Color", self::getRandomColor());
		}
		parent::__construct($chunk, $nbt);

		$this->setDataProperty(self::DATA_COLOR_INFO, self::DATA_TYPE_BYTE, $this->getColor());
	}

	public static function getRandomColor() : int{
		$rand = "";
		$rand .= str_repeat(Wool::WHITE . " ", 20);
		$rand .= str_repeat(Wool::ORANGE . " ", 5);
		$rand .= str_repeat(Wool::MAGENTA . " ", 5);
		$rand .= str_repeat(Wool::LIGHT_BLUE . " ", 5);
		$rand .= str_repeat(Wool::YELLOW . " ", 5);
		$rand .= str_repeat(Wool::GRAY . " ", 10);
		$rand .= str_repeat(Wool::LIGHT_GRAY . " ", 10);
		$rand .= str_repeat(Wool::CYAN . " ", 5);
		$rand .= str_repeat(Wool::PURPLE . " ", 5);
		$rand .= str_repeat(Wool::BLUE . " ", 5);
		$rand .= str_repeat(Wool::BROWN . " ", 5);
		$rand .= str_repeat(Wool::GREEN . " ", 5);
		$rand .= str_repeat(Wool::RED . " ", 5);
		$rand .= str_repeat(Wool::BLACK . " ", 10);
		$arr = explode(" ", $rand);
		return intval($arr[mt_rand(0, count($arr) - 1)]);
	}

	public function getColor() : int{
		return (int) $this->namedtag["Color"];
	}

	public function setColor(int $color){
		$this->namedtag->Color = new ByteTag("Color", $color);
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Sheep::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
	
	public function getDrops(){
		$drops = [
			ItemItem::get(ItemItem::WOOL, $this->getColor(), 1)
		];
		return $drops;
	}
}