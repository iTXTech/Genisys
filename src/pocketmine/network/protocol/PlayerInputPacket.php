<?php

namespace pocketmine\network\protocol;

class PlayerInputPacket extends DataPacket{
	const NETWORK_ID = Info::PLAYER_INPUT_PACKET;

	public $motX;
	public $motY;

	public $jumping;
	public $sneaking;

	public function decode(){
		$this->motX = $this->getFloat();
		$this->motY = $this->getFloat();
		$flags = $this->getByte();
		$this->jumping = (($flags & 0x80) > 0);
		$this->sneaking = (($flags & 0x40) > 0);
	}

	public function encode(){

	}

}
