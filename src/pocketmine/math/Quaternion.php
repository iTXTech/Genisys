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

namespace pocketmine\math;

class Quaternion{
	public $x;
	public $y;
	public $z;
	public $w;
	private $hashed = false;
	private $hashCode = 0;

	public function __construct($x = 0, $y = 0, $z = 0, $w = 1){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->w = $w;
	}

	public function getX(){
		return $this->x;
	}

	public function getY(){
		return $this->y;
	}

	public function getZ(){
		return $this->z;
	}

	public function getW(){
		return $this->w;
	}

	/**
	 * @param Quaternion|int $x
	 * @param int         $y
	 * @param int         $z
	 * @param int         $w
	 *
	 * @return Quaternion
	 */
	public function add($x, $y = 0, $z = 0, $w = 0){
		if($x instanceof Quaternion){
			return new Quaternion($this->x + $x->x, $this->y + $x->y, $this->z + $x->z, $this->w + $x->w);
		}else{
			return new Quaternion($this->x + $x, $this->y + $y, $this->z + $z, $this->w + $w);
		}
	}

	/**
	 * @param Quaternion|int $x
	 * @param int         $y
	 * @param int         $z
	 * @param int         $w
	 *
	 * @return Quaternion
	 */
	public function subtract($x = 0, $y = 0, $z = 0, $w = 0){
		if($x instanceof Quaternion){
			return $this->add(-$x->x, -$x->y, -$x->z, -$x->w);
		}else{
			return $this->add(-$x, -$y, -$z, -$w);
		}
	}

	public function multiply($x, $y = 0, $z = 0, $w = 0){
		if($y == 0 and $z == 0 and $w == 0){
			return new Quaternion($this->x * $x, $this->y * $x, $this->z * $x, $this->w * $x);
		}
		return new Quaternion(
			$this->w * $x + $this->x * $w + $this->y * $z - $this->z * $y,
			$this->w * $y + $this->y * $w + $this->z * $x - $this->x * $z,
			$this->w * $z + $this->z * $w + $this->x * $y - $this->y * $z,
			$this->w * $w - $this->x * $x - $this->y * $y - $this->z * $z);
	}

	public function divide($number){
		return new Quaternion($this->x / $number, $this->y / $number, $this->z / $number, $this->w / $number);
	}

	public function length(){
		return Math::length($this->x, $this->y, $this->z, $this->w);
	}

	public function normalize(){
		$length = $this->length();
		return new Quaternion($this->x / $length, $this->y / $length, $this->z / $length, $this->w / $length);
	}

	public static function fromAngleRadAxis($angle, $x, $y, $z){
		$halfAngle = $angle / 2;
		$q = sin($halfAngle) / sqrt($x * $x + $y * $y + $z * $z);
		return new Quaternion($x * $q, $y * $q, $z * $q, cos($halfAngle));
	}
}