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

class UNCONNECTED_PING extends Packet{
    public static $ID = 0x01;

    public $pingID;

    public function encode(){
        parent::encode();
        $this->buffer .= Binary::writeLong($this->pingID);
        $this->buffer .= RakLib::MAGIC;
    }

    public function decode(){
        parent::decode();
        $this->pingID = Binary::readLong($this->get(8));
        //magic
    }
}
