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

namespace pocketmine\level\generator\structure;

use pocketmine\math\Matrix3;
use pocketmine\math\Quaternion;
use pocketmine\math\Vector3;

class PieceLayoutBuilder extends PieceBuilder{
	/** @var Vector3 */
	private $position;
	/** @var Quaternion */
	private $rotation;
	/** @var Vector3 */
	private $rotationPoint;
	/** @var BlockLayout */
	private $layout;

	public function __construct(StructurePiece $parent){
		$this->position = new Vector3(0, 0, 0);
		$this->rotation = new Quaternion();
		$this->rotationPoint = new Vector3(0, 0, 0);
		$this->layout = new BlockLayout("");
		parent::__construct($parent);
	}

	public function setLayout(BlockLayout $layout){
		$this->layout = $layout;
		return $this;
	}

	public function setPosition(int $x, int $y, int $z){
		$this->position->setComponents($x, $y, $z);
		return $this;
	}

	public function offsetPosition(int $xOff, int $yOff, int $zOFf){
		$this->position->add($xOff, $yOff, $zOFf);
		return $this;
	}

	public function setRotation(Quaternion $rotation){
		$this->rotation = $rotation;
	}

	public function fill(){
		for($xx = 0; $xx < $this->layout->getRowLength(); $xx++){
			for($zz = 0; $zz < $this->layout->getColumnLength($xx); $zz++){
				$id = $this->layout->getBlockId($xx, $zz);
				if($id != null){
					$this->setBlockId($xx, $zz, $id);
				}
			}
		}
	}

	private function setBlockId(int $xx, int $zz, int $id){
		$transformed = $this->transform($xx, $zz);
		$this->parent->getLevel()->setBlockIdAt($transformed->getFloorX(), $transformed->getFloorY(), $transformed->getFloorZ(), $id);
	}

	private function transform(int $x, int $z) : Vector3{
		$rotPoint = new Vector3($this->rotationPoint->getX(), $this->rotationPoint->getY(), $this->rotationPoint->getZ());
		return Matrix3::createRotation($this->rotation)->transform((new Vector3($x, 0, $z))->subtract($rotPoint))->
		add($rotPoint)->add($this->position->getX(), $this->position->getY(), $this->position->getZ())->round();
	}
}