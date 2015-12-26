<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

namespace raklib\protocol;

use raklib\Binary;










use raklib\RakLib;

class UNCONNECTED_PONG extends Packet{
    public static $ID = 0x1c;

    public $pingID;
    public $serverID;
    public $serverName;

    public function encode(){
        parent::encode();
        $this->buffer .= Binary::writeLong($this->pingID);
        $this->buffer .= Binary::writeLong($this->serverID);
        $this->buffer .= RakLib::MAGIC;
        $this->putString($this->serverName);
    }

    public function decode(){
        parent::decode();
        $this->pingID = Binary::readLong($this->get(8));
        $this->serverID = Binary::readLong($this->get(8));
        $this->offset += 16; //magic
        $this->serverName = $this->getString();
    }
}
