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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


class StartGamePacket extends DataPacket{
	const NETWORK_ID = Info::START_GAME_PACKET;

	public $entityUniqueId;
	public $eid;
	public $x;
	public $y;
	public $z;
	public $seed;
	public $dimension;
	public $generator;
	public $gamemode;
	public $difficulty;
	public $hasBeenLoadedInCreative = true;
	public $eduMode = false;
	public $rainLevel = 0;
	public $lightningLevel = 0;
	public $commandsEnabled = false; //to prevent client crash (temporary fix)

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putVarInt($this->entityUniqueId); //EntityUniqueID
		$this->putVarInt($this->eid); //EntityRuntimeID (basically just the normal entityID)
		$this->putFloat($this->x);
		$this->putFloat($this->y);
		$this->putFloat($this->z);
		$this->putVarInt($this->seed);
		$this->putByte($this->dimension);
		$this->putByte($this->generator);
		$this->putByte($this->gamemode);
		$this->putByte($this->difficulty); //Difficulty (TODO)
		$this->putByte($this->hasBeenLoadedInCreative); //has been loaded in creative
		$this->putByte($this->eduMode); //edu mode
		$this->putFloat($this->rainLevel); //rain level
		$this->putFloat($this->lightningLevel); //lightning level
		$this->putByte($this->commandsEnabled); //commands enabled
		$this->putString("");
	}

}
