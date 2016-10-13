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

namespace pocketmine\tile;

use pocketmine\level\format\FullChunk;
use pocketmine\level\sound\NoteblockSound;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class Music extends Spawnable{
	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt->powered)){
			$nbt->powered = new ByteTag("powered", 0);
		}

		if(!isset($nbt->note)){
			$nbt->note = new ByteTag("note", 0);
		}

		parent::__construct($chunk, $nbt);
	}

	/**
	 * @return \pocketmine\block\Noteblock
	 */
	public function getBlock(){
		return $this->level->getBlock($this);
	}

	public function isPowered() : bool{
		return ($this->namedtag["powered"] == 1) ? true : false;
	}

	public function setPowered(bool $powered){
		$this->namedtag->powered = new ByteTag("powered", $powered ? 1 : 0);
		if($powered){
			$this->play();
		}
	}

	public function getNote() : int{
		return (int)$this->namedtag["note"];
	}

	public function setNote(int $note){
		$this->namedtag->note = new ByteTag("note", $note % 25);
	}

	public function play(){
		$this->getLevel()->addSound(new NoteblockSound($this, $this->getBlock()->getInstrument(), $this->getNote()));
	}

	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::NOTE_BLOCK),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new ByteTag("powered", $this->isPowered() ? 1 : 0),
			new ByteTag("note", $this->getNote())
		]);
	}
}