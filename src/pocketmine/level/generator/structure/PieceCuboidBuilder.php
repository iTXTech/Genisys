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
use pocketmine\math\Vector3;

class PieceCuboidBuilder extends PieceBuilder{
	/** @var Vector3 */
	protected $min;
	/** @var Vector3 */
	protected $max;
	/** @var BlockPicker */
	private $picker;
	private $ignoreAir = false;

	public function __construct(StructurePiece $parent){
		$this->min = new Vector3(0, 0, 0);
		$this->max = new Vector3(0, 0, 0);
		$this->picker = new SimpleBlockPicker();
		parent::__construct($parent);
	}

	public function setMax(int $x, int $y, int $z){
		$this->max->setComponents($x, $y, $z);
		return $this;
	}

	public function setMin(int $x, int $y, int $z){
		$this->min->setComponents($x, $y, $z);
		return $this;
	}

	public function setMinMax(int $minX, int $minY, int $minZ, int $maxX, int $maxY, int $maxZ){
		$this->setMin($minX, $minY, $minZ);
		$this->setMin($maxX, $maxY, $maxZ);
		return $this;
	}

	public function offsetMax(int $x, int $y, int $z){
		$this->max->add($x, $y, $z);
		return $this;
	}

	public function offsetMin(int $x, int $y, int $z){
		$this->min->add($x, $y, $z);
		return $this;
	}

	public function offsetMinMax(int $minX, int $minY, int $minZ, int $maxX, int $maxY, int $maxZ){
		$this->offsetMin($minX, $minY, $minZ);
		$this->offsetMin($maxX, $maxY, $maxZ);
		return $this;
	}

	public function setPicker(BlockPicker $picker){
		$this->picker = $picker;
		return $this;
	}

	public function toggleIgnoreAir(){
		$this->ignoreAir ^= true;
		return $this;
	}

	protected function isOuter(int $xx, int $yy, int $zz){
		return $xx == $this->min->getX() or $yy == $this->min->getY() or $zz == $this->min->getZ()
				or $xx == $this->max->getX() or $yy = $this->max->getY() or $zz == $this->max->getZ();
	}

	public function fill(){
		$endX = $this->max->getX();
		$endY = $this->max->getY();
		$endZ = $this->max->getZ();
		for($xx = $this->min->getX(); $xx <= $endX; $xx++){
			for($yy = $this->min->getY(); $yy <= $endY; $yy++){
				for($zz = $this->min->getZ(); $zz <= $endZ; $zz++){
					if(!$this->ignoreAir or !$this->parent->getBlockIdAt($xx, $yy, $zz) == Block::AIR){
						$this->parent->setBlockIdAt($xx, $yy, $zz, $this->picker->get($this->isOuter($xx, $yy, $zz)));
					}
				}
			}
		}
	}

	public function randomFill(float $odd){
		$endX = $this->max->getX();
		$endY = $this->max->getY();
		$endZ = $this->max->getZ();
		for($xx = $this->min->getX(); $xx <= $endX; $xx++){
			for($yy = $this->min->getY(); $yy <= $endY; $yy++){
				for($zz = $this->min->getZ(); $zz <= $endZ; $zz++){
					if($this->parent->getRandom()->nextFloat() > $odd){
						continue;
					}
					if(!$this->ignoreAir or !$this->parent->getBlockIdAt($xx, $yy, $zz) == Block::AIR){
						$this->parent->setBlockIdAt($xx, $yy, $zz, $this->picker->get($this->isOuter($xx, $yy, $zz)));
					}
				}
			}
		}
	}

	public function sphericalFill(){
		$xScale = $this->max->getX() - $this->min->getX() + 1;
		$yScale = $this->max->getY() - $this->min->getY() + 1;
		$zScale = $this->max->getZ() - $this->min->getZ() + 1;
		$xOffset = $this->min->getX() + $xScale / 2;
		$zOffset = $this->min->getZ() + $zScale / 2;
		$endX = $this->max->getX();
		$endY = $this->max->getY();
		$endZ = $this->max->getZ();
		for($xx = $this->min->getX(); $xx <= $endX; $xx++){
			$dx = ($xx - $xOffset) / ($xScale * 0.5);
			for($yy = $this->min->getY(); $yy <= $endY; $yy++){
				$dy = ($yy - $this->min->getY()) / $yScale;
				for($zz = $this->min->getZ(); $zz <= $endZ; $zz++){
					$dz = ($zz - $zOffset) / ($zScale * 0.5);
					if(($dx * $dx + $dy * $dy + $dz * $dz) <= 1.05){
						if($this->ignoreAir and $this->parent->getBlockIdAt($xx, $yy, $zz) == Block::AIR){
							continue;
						}
						$this->parent->setBlockIdAt($xx, $yy, $zz, $this->picker->get(false));
					}
				}
			}
		}
	}

	public function intersectsLiquids() : bool{
		$startX = $this->min->getX();
		$startY = $this->min->getY();
		$startZ = $this->min->getZ();
		$endX = $this->max->getX();
		$endY = $this->max->getY();
		$endZ = $this->max->getZ();
		for($yy = $startY; $yy <= $endY; $yy++){
			if($yy == $startY or $yy == $endY){
				for($xx = $startX; $xx <= $endX; $xx++){
					for($zz = $startZ; $zz <= $endZ; $zz++){
						if(self::isLiquid($this->parent->getBlockIdAt($xx, $yy, $zz))){
							return true;
						}
					}
				}
			}else{
				for($xx = $startX; $xx <= $endX; $xx++){
					if(self::isLiquid($this->parent->getBlockIdAt($xx, $yy, $startZ))
						or self::isLiquid($this->parent->getBlockIdAt($xx, $yy, $endZ))){
						return true;
					}
				}
				for($zz = $startZ + 1; $zz < $endZ; $zz++){
					if(self::isLiquid($this->parent->getBlockIdAt($startX, $yy, $zz))
						or self::isLiquid($this->parent->getBlockIdAt($endX, $yy, $zz))){
						return true;
					}
				}
			}
		}
		return false;
	}

	public static function isLiquid(int $id) : bool{
		if($id == Block::WATER or $id == Block::LAVA or $id == Block::STILL_LAVA or $id == Block::STILL_WATER){
			return true;
		}
		return false;
	}
}