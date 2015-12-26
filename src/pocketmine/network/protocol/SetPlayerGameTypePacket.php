<?php
namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>

class SetPlayerGameTypePacket extends DataPacket {

	const NETWORK_ID = Info::SET_PLAYER_GAMETYPE_PACKET;

	public $gamemode;

	public function decode() {

	}

	public function encode() {
		$this->reset();
		$this->putInt($this->gamemode);
	}
}