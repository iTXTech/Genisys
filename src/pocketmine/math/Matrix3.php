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

class Matrix3 extends Matrix{

	public function __construct(
		$m00, $m01, $m02,
		$m10, $m11, $m12,
		$m20, $m21, $m22){
		parent::__construct(3, 3, [
			$m00, $m01, $m02,
			$m10, $m11, $m12,
			$m20, $m21, $m22
		]);
	}

	public function transform($x, $y = 0, $z = 0) : Vector3{
		if($x instanceof Vector3){
			return $this->transform($x->getX(), $x->getY(), $x->getZ());
		}
		return new Vector3(
			$this->getElement(0, 0) * $x + $this->getElement(0, 1) * $y, $this->getElement(0, 2) * $z,
			$this->getElement(1, 0) * $x + $this->getElement(1, 1) * $y, $this->getElement(1, 2) * $z,
			$this->getElement(2, 0) * $x + $this->getElement(2, 1) * $y, $this->getElement(2, 2) * $z
		);
	}

	public static function createRotation(Quaternion $rot){
		$rot = $rot->normalize();
		return new Matrix3(
			1 - 2 * $rot->getY() * $rot->getY() - 2 * $rot->getZ() * $rot->getZ(),
			2 * $rot->getX() * $rot->getY() - 2 * $rot->getW() * $rot->getZ(),
			2 * $rot->getX() * $rot->getZ() + 2 * $rot->getW() * $rot->getY(),
			2 * $rot->getX() * $rot->getY() + 2 * $rot->getW() * $rot->getZ(),
			1 - 2 * $rot->getX() * $rot->getX() - 2 * $rot->getZ() * $rot->getZ(),
			2 * $rot->getY() * $rot->getZ() - 2 * $rot->getW() * $rot->getX(),
			2 * $rot->getX() * $rot->getZ() - 2 * $rot->getW() * $rot->getY(),
			2 * $rot->getY() * $rot->getZ() + 2 * $rot->getX() * $rot->getW(),
			1 - 2 * $rot->getX() * $rot->getX() - 2 * $rot->getY() * $rot->getY()
		);
	}
}