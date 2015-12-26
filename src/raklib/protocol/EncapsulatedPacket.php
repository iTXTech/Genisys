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

namespace raklib\protocol;


use raklib\Binary;









class EncapsulatedPacket{

    public $reliability;
    public $hasSplit = \false;
    public $length = 0;
    public $messageIndex = \null;
    public $orderIndex = \null;
    public $orderChannel = \null;
    public $splitCount = \null;
    public $splitID = \null;
    public $splitIndex = \null;
    public $buffer;
    public $needACK = \false;
    public $identifierACK = \null;

    /**
     * @param string $binary
     * @param bool   $internal
     * @param int    &$offset
     *
     * @return EncapsulatedPacket
     */
    public static function fromBinary($binary, $internal = \false, &$offset = \null){

	    $packet = new EncapsulatedPacket();

        $flags = \ord($binary{0});
        $packet->reliability = $reliability = ($flags & 0b11100000) >> 5;
        $packet->hasSplit = $hasSplit = ($flags & 0b00010000) > 0;
        if($internal){
            $length = (\PHP_INT_SIZE === 8 ? \unpack("N", \substr($binary, 1, 4))[1] << 32 >> 32 : \unpack("N", \substr($binary, 1, 4))[1]);
            $packet->identifierACK = (\PHP_INT_SIZE === 8 ? \unpack("N", \substr($binary, 5, 4))[1] << 32 >> 32 : \unpack("N", \substr($binary, 5, 4))[1]);
            $offset = 9;
        }else{
            $length = (int) \ceil(\unpack("n", \substr($binary, 1, 2))[1] / 8);
            $offset = 3;
	        $packet->identifierACK = \null;
        }


        /*
         * From http://www.jenkinssoftware.com/raknet/manual/reliabilitytypes.html
         *
         * Default: 0b010 (2) or 0b011 (3)
         *
         * 0: UNRELIABLE
         * 1: UNRELIABLE_SEQUENCED
         * 2: RELIABLE
         * 3: RELIABLE_ORDERED
         * 4: RELIABLE_SEQUENCED
         * 5: UNRELIABLE_WITH_ACK_RECEIPT
         * 6: RELIABLE_WITH_ACK_RECEIPT
         * 7: RELIABLE_ORDERED_WITH_ACK_RECEIPT
         */

		if($reliability > 0){
			if($reliability >= 2 and $reliability !== 5){
				$packet->messageIndex = \unpack("V", \substr($binary, $offset, 3) . "\x00")[1];
				$offset += 3;
			}

			if($reliability <= 4 and $reliability !== 2){
				$packet->orderIndex = \unpack("V", \substr($binary, $offset, 3) . "\x00")[1];
				$offset += 3;
				$packet->orderChannel = \ord($binary{$offset++});
			}
		}

        if($hasSplit){
            $packet->splitCount = (\PHP_INT_SIZE === 8 ? \unpack("N", \substr($binary, $offset, 4))[1] << 32 >> 32 : \unpack("N", \substr($binary, $offset, 4))[1]);
            $offset += 4;
            $packet->splitID = \unpack("n", \substr($binary, $offset, 2))[1];
            $offset += 2;
            $packet->splitIndex = (\PHP_INT_SIZE === 8 ? \unpack("N", \substr($binary, $offset, 4))[1] << 32 >> 32 : \unpack("N", \substr($binary, $offset, 4))[1]);
            $offset += 4;
        }

        $packet->buffer = \substr($binary, $offset, $length);
        $offset += $length;

        return $packet;
    }

    public function getTotalLength(){
        return 3 + \strlen($this->buffer) + ($this->messageIndex !== \null ? 3 : 0) + ($this->orderIndex !== \null ? 4 : 0) + ($this->hasSplit ? 10 : 0);
    }

    /**
     * @param bool $internal
     *
     * @return string
     */
    public function toBinary($internal = \false){
        return
			\chr(($this->reliability << 5) | ($this->hasSplit ? 0b00010000 : 0)) .
			($internal ? \pack("N", \strlen($this->buffer)) . \pack("N", $this->identifierACK) : \pack("n", \strlen($this->buffer) << 3)) .
			($this->reliability > 0 ?
				(($this->reliability >= 2 and $this->reliability !== 5) ? \substr(\pack("V", $this->messageIndex), 0, -1) : "") .
				(($this->reliability <= 4 and $this->reliability !== 2) ? \substr(\pack("V", $this->orderIndex), 0, -1) . \chr($this->orderChannel) : "")
				: ""
			) .
			($this->hasSplit ? \pack("N", $this->splitCount) . \pack("n", $this->splitID) . \pack("N", $this->splitIndex) : "")
			. $this->buffer;
    }

    public function __toString(){
        return $this->toBinary();
    }
}
