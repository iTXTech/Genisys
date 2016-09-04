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

use pocketmine\block\Block;
use pocketmine\level\generator\object\Object;
use pocketmine\math\Matrix3;
use pocketmine\math\Quaternion;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

abstract class StructurePiece{
	/** @var Structure */
	protected $parent;
	/** @var Vector3 */
	protected $position;
	/** @var Quaternion */
	protected $rotation;
	/** @var Vector3 */
	protected $rotationPoint;

	public function __construct(Structure $parent){
		$this->parent = $parent;
		$this->position = new Vector3(0, 0, 0);
		$this->rotation = new Quaternion();
		$this->rotationPoint = new Vector3(0, 0, 0);
	}

	public function getRandom() : Random{
		return $this->parent->getRandom();
	}

	public function getParent() : Structure{
		return $this->parent;
	}

	public function rotate(int $x, int $y, int $z){
		return Matrix3::createRotation($this->rotation)->transform((new Vector3($x, $y, $z))->subtract($this->rotationPoint))->add($this->rotationPoint);
	}

	public function transform(int $x, int $y, int $z) : Vector3{
		return $this->rotate($x, $y, $z)->add($this->position)->round();
	}

	public function getBlockIdAt(int $xx, int $yy, int $zz){
		$pos = $this->transform($xx, $yy, $zz);
		return $this->parent->getLevel()->getBlockIdAt($pos->x, $pos->y, $pos->z);
	}

	public function getBlockDataAt(int $xx, int $yy, int $zz){
		$pos = $this->transform($xx, $yy, $zz);
		return $this->parent->getLevel()->getBlockDataAt($pos->x, $pos->y, $pos->z);
	}

	public function setBlockIdAt(int $xx, int $yy, int $zz, int $id){
		$transformed = $this->transform($xx, $yy, $zz);
		$this->parent->getLevel()->setBlockIdAt($transformed->getFloorX(), $transformed->getFloorY(), $transformed->getFloorZ(), $id);
		//TODO: block face check
	}

	public function setBlockDataAt(int $xx, int $yy, int $zz, int $meta){
		$transformed = $this->transform($xx, $yy, $zz);
		$this->parent->getLevel()->setBlockDataAt($transformed->getFloorX(), $transformed->getFloorY(), $transformed->getFloorZ(), $meta);
	}

	public function setBlockIdAtWithOdd(float $odd, int $xx, int $yy, int $zz, int $id){
		if($this->getRandom()->nextFloat() > $odd){
			$this->setBlockIdAt($xx, $yy, $zz, $id);
		}
	}

	public function attachBlock(int $xx, int $yy, int $zz, int $id){
		//TODO: Implement
		$this->setBlockIdAt($xx, $yy, $zz, $id);
	}

	public function fillDownwards(int $xx, int $yy, int $zz, int $limit, int $id){
		$counter = 0;
		while((($block = $this->getBlockIdAt($xx, $yy, $zz)) == Block::AIR
				or PieceCuboidBuilder::isLiquid($block)) and $counter++ < $limit){
			$this->setBlockIdAt($xx, $yy, $zz, $id);
			$yy--;
		}
	}

	public function placeObject(int $xx, int $yy, int $zz, Object $object){
		$transformed = $this->transform($xx, $yy, $zz);
		if($object->canPlaceObject($this->parent->getLevel(), $transformed->getFloorX(), $transformed->getFloorY(), $transformed->getFloorZ())){
			$object->placeObject($this->parent->getLevel(), $transformed->getFloorX(), $transformed->getFloorY(), $transformed->getFloorZ());
		}
	}

	public function placeDoor(int $xx, int $yy, int $zz, int $doorId, int $face){
		//TODO: Implement
		$this->setBlockIdAt($xx, $yy, $zz, $doorId);
	}

	public function getPosition() : Vector3{
		return $this->position;
	}

	public function setPosition(Vector3 $pos){
		$this->position = $pos;
	}

	public function getRotation() : Quaternion{
		return $this->rotation;
	}

	public abstract function canPlace() : bool;

	public abstract function place();

	public abstract function randomize();

	public abstract function getNextPieces();

	public abstract function getBoundingBox();
}