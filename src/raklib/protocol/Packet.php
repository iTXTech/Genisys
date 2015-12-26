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









abstract class Packet{
    public static $ID = -1;

    protected $offset = 0;
    public $buffer;
    public $sendTime;

    protected function get($len){
        if($len < 0){
            $this->offset = \strlen($this->buffer) - 1;

            return "";
        }elseif($len === \true){
            return \substr($this->buffer, $this->offset);
        }

        $buffer = "";
        for(; $len > 0; --$len, ++$this->offset){
            $buffer .= $this->buffer{$this->offset};
        }

        return $buffer;
    }

    protected function getLong($signed = \true){
        return Binary::readLong($this->get(8), $signed);
    }

    protected function getInt(){
        return (\PHP_INT_SIZE === 8 ? \unpack("N", $this->get(4))[1] << 32 >> 32 : \unpack("N", $this->get(4))[1]);
    }

    protected function getShort($signed = \true){
        return $signed ? (\PHP_INT_SIZE === 8 ? \unpack("n", $this->get(2))[1] << 48 >> 48 : \unpack("n", $this->get(2))[1] << 16 >> 16) : \unpack("n", $this->get(2))[1];
    }

    protected function getTriad(){
        return \unpack("N", "\x00" . $this->get(3))[1];
    }

    protected function getLTriad(){
        return \unpack("V", $this->get(3) . "\x00")[1];
    }

    protected function getByte(){
        return \ord($this->buffer{$this->offset++});
    }

    protected function getString(){
        return $this->get(\unpack("n", $this->get(2))[1]);
    }

    protected function getAddress(&$addr, &$port, &$version = \null){
		$version = \ord($this->get(1));
		if($version === 4){
			$addr = ((~\ord($this->get(1))) & 0xff) .".". ((~\ord($this->get(1))) & 0xff) .".". ((~\ord($this->get(1))) & 0xff) .".". ((~\ord($this->get(1))) & 0xff);
			$port = \unpack("n", $this->get(2))[1];
		}else{
			//TODO: IPv6
		}
	}

    protected function feof(){
        return !isset($this->buffer{$this->offset});
    }

    protected function put($str){
        $this->buffer .= $str;
    }

    protected function putLong($v){
        $this->buffer .= Binary::writeLong($v);
    }

    protected function putInt($v){
        $this->buffer .= \pack("N", $v);
    }

    protected function putShort($v){
        $this->buffer .= \pack("n", $v);
    }

    protected function putTriad($v){
        $this->buffer .= \substr(\pack("N", $v), 1);
    }

    protected function putLTriad($v){
        $this->buffer .= \substr(\pack("V", $v), 0, -1);
    }

    protected function putByte($v){
        $this->buffer .= \chr($v);
    }

    protected function putString($v){
        $this->buffer .= \pack("n", \strlen($v));
        $this->buffer .= $v;
    }
    
    protected function putAddress($addr, $port, $version = 4){
		$this->buffer .= \chr($version);
		if($version === 4){
			foreach(\explode(".", $addr) as $b){
				$this->buffer .= \chr((~((int) $b)) & 0xff);
			}
			$this->buffer .= \pack("n", $port);
		}else{
			//IPv6
		}
	}

    public function encode(){
        $this->buffer = \chr(static::$ID);
    }

    public function decode(){
        $this->offset = 1;
    }

	public function clean(){
		$this->buffer = \null;
		$this->offset = 0;
		$this->sendTime = \null;
		return $this;
	}
}
