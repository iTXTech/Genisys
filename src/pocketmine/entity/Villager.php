<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\entity;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\level\format\Chunk;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;

class Villager extends Creature implements NPC, Ageable{
	const PROFESSION_FARMER = 0;
	const PROFESSION_LIBRARIAN = 1;
	const PROFESSION_PRIEST = 2;
	const PROFESSION_BLACKSMITH = 3;
	const PROFESSION_BUTCHER = 4;
	//const PROFESSION_GENERIC = 5;

	const NETWORK_ID = 15;

	const DATA_PROFESSION_ID = 16;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;

	public function getName() : string{
		return "Villager";
	}

	public function __construct(Chunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->Profession)){
			$nbt->Profession = new ByteTag("Profession", mt_rand(0, 4));
		}

		parent::__construct($chunk, $nbt);

		$this->setDataProperty(self::DATA_PROFESSION_ID, self::DATA_TYPE_BYTE, $this->getProfession());
	}

	protected function initEntity(){
		parent::initEntity();
		if(!isset($this->namedtag->Profession)){
			$this->setProfession(self::PROFESSION_FARMER);
		}
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Villager::NETWORK_ID;
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

	/**
	 * Sets the villager profession
	 *
	 * @param int $profession
	 */
	public function setProfession(int $profession){
		$this->namedtag->Profession = new ByteTag("Profession", $profession);
	}

	public function getProfession() : int{
		$pro = (int) $this->namedtag["Profession"];
		return min(4, max(0, $pro));
	}

	public function isBaby(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
	}
}
