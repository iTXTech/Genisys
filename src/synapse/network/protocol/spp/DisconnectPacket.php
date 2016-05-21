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
 
namespace synapse\network\protocol\spp;

class DisconnectPacket extends DataPacket{
	const NETWORK_ID = Info::DISCONNECT_PACKET;

	const TYPE_WRONG_PROTOCOL = 0;
	const TYPE_GENERIC = 1;

	public $type;
	public $message;

	public function encode(){
		$this->reset();
		$this->putByte($this->type);
		$this->putString($this->message);
	}

	public function decode(){
		$this->type = $this->getByte();
		$this->message = $this->getString();
	}
}