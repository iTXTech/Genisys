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

class OPEN_CONNECTION_REQUEST_1 extends Packet{
    public static $ID = 0x05;

    public $protocol = RakLib::PROTOCOL;
    public $mtuSize;

    public function encode(){
        parent::encode();
        $this->buffer .= RakLib::MAGIC;
        $this->buffer .= \chr($this->protocol);
        $this->buffer .= \str_repeat(\chr(0x00), $this->mtuSize - 18);
    }

    public function decode(){
        parent::decode();
        $this->offset += 16; //Magic
        $this->protocol = \ord($this->get(1));
        $this->mtuSize = \strlen($this->get(\true)) + 18;
    }
}
