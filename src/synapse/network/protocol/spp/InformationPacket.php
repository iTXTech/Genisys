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

class InformationPacket extends DataPacket{
	const NETWORK_ID = Info::INFORMATION_PACKET;

	const TYPE_LOGIN = 0;
	const TYPE_CLIENT_DATA = 1;

	const INFO_LOGIN_SUCCESS = "success";
	const INFO_LOGIN_FAILED = "failed";

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