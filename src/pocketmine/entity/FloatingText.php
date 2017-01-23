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

use pocketmine\item\Item as ItemItem;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class FloatingText extends Entity{
	const NETWORK_ID = 1000; // Just a random unused ID

	protected $title;
	protected $text;

	public function getName(): string{
		return "FloatingText";
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function setText($text){
		$this->text = $text;
	}

	public function spawnTo(Player $player){
		$pk = new AddPlayerPacket();
		$pk->eid = $this->getId();
		$pk->uuid = UUID::fromRandom();
		$pk->x = $this->x;
		$pk->y = $this->y - 1.62;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->item = ItemItem::get(ItemItem::AIR);
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 << Entity::DATA_FLAG_INVISIBLE],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
			Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
			Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
			Entity::DATA_LEAD => [Entity::DATA_TYPE_BYTE, 0]
		];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function saveNBT(){

	}
}
