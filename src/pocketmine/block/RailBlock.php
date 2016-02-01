<?php
namespace pocketmine\block;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\item\Tool;
use pocketmine\level\Level;
abstract class RailBlock extends Flowable{
	const SIDE_NORTH_WEST = 6;
	const SIDE_NORTH_EAST = 7;
	const SIDE_SOUTH_EAST = 8;
	const SIDE_SOUTH_WEST = 9;
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $block->getSide(Vector3::SIDE_DOWN);
		$class = "";
		if($down->isTransparent() === false){
			$up = $block->getSide(Vector3::SIDE_UP);
			if($block->getSide(Vector3::SIDE_EAST) instanceof RailBlock && $block->getSide(Vector3::SIDE_SOUTH) instanceof RailBlock){
				return $this->setDirection(self::SIDE_SOUTH_EAST);
			}
			elseif($block->getSide(Vector3::SIDE_EAST) instanceof RailBlock && $block->getSide(Vector3::SIDE_NORTH) instanceof RailBlock){
				return $this->setDirection(self::SIDE_NORTH_EAST);
			}
			elseif($block->getSide(Vector3::SIDE_SOUTH) instanceof RailBlock && $block->getSide(Vector3::SIDE_WEST) instanceof RailBlock){
				return $this->setDirection(self::SIDE_SOUTH_WEST);
			}
			elseif($block->getSide(Vector3::SIDE_NORTH) instanceof RailBlock && $block->getSide(Vector3::SIDE_WEST) instanceof RailBlock){
				return $this->setDirection(self::SIDE_NORTH_WEST);
			}
			elseif($block->getSide(Vector3::SIDE_EAST) instanceof RailBlock && $block->getSide(Vector3::SIDE_WEST) instanceof RailBlock){
				if($up->getSide(Vector3::SIDE_EAST) instanceof RailBlock){
					return $this->setDirection(Vector3::SIDE_EAST, true);
				}
				elseif($up->getSide(Vector3::SIDE_WEST) instanceof RailBlock){
					return $this->setDirection(Vector3::SIDE_WEST, true);
				}
				else{
					return $this->setDirection(Vector3::SIDE_EAST);
				}
			}
			elseif($block->getSide(Vector3::SIDE_SOUTH) instanceof RailBlock && $block->getSide(Vector3::SIDE_NORTH) instanceof RailBlock){
				if($up->getSide(Vector3::SIDE_SOUTH) instanceof RailBlock){
					return $this->setDirection(Vector3::SIDE_SOUTH, true);
				}
				elseif($up->getSide(Vector3::SIDE_NORTH) instanceof RailBlock){
					return $this->setDirection(Vector3::SIDE_NORTH, true);
				}
				else{
					return $this->setDirection(Vector3::SIDE_SOUTH);
				}
			}
			else{
				return $this->setDirection(Vector3::SIDE_NORTH);
			}
		}
		return false;
	}
	public function getDirection(){
		switch($this->meta){
			case 0:
				{
					return Vector3::SIDE_SOUTH;
				}
				break;
			case 1:
				{
					return Vector3::SIDE_EAST;
				}
				break;
			case 2:
				{
					return Vector3::SIDE_EAST;
				}
				break;
			case 3:
				{
					return Vector3::SIDE_WEST;
				}
				break;
			case 4:
				{
					return Vector3::SIDE_NORTH;
				}
				break;
			case 5:
				{
					return Vector3::SIDE_SOUTH;
				}
				break;
			case 6:
				{
					return self::SIDE_NORTH_WEST;
				}
				break;
			case 7:
				{
					return self::SIDE_NORTH_EAST;
				}
				break;
			case 8:
				{
					return self::SIDE_SOUTH_EAST;
				}
				break;
			case 9:
				{
					return self::SIDE_SOUTH_WEST;
				}
				break;
			default:
				{
					return Vector3::SIDE_SOUTH;
				}
		}
	}
	public function __toString(){
		$this->getName() . " facing " . $this->getDirection() . ($this->isCurve()?" on a curve ":($this->isOnSlope()?" on a slope":""));
	}
	public function setDirection($face, $isOnSlope = false){
		$class = "";
		switch($face){
			case Vector3::SIDE_EAST:
				{
					$meta = $isOnSlope?2:1;
				}
				break;
			case Vector3::SIDE_WEST:
				{
					$meta = $isOnSlope?3:1;
				}
				break;
			case Vector3::SIDE_NORTH:
				{
					$meta = $isOnSlope?4:0;
				}
				break;
			case Vector3::SIDE_SOUTH:
				{
					$meta = $isOnSlope?5:0;
				}
				break;
			case self::SIDE_NORTH_WEST:
				{
					$meta = 6;
				}
				break;
			case self::SIDE_NORTH_EAST:
				{
					$meta = 7;
				}
				break;
			case self::SIDE_SOUTH_EAST:
				{
					$meta = 8;
				}
				break;
			case self::SIDE_SOUTH_WEST:
				{
					$meta = 9;
				}
				break;
			default:
				{
					$meta = 0;
				}
		}
		return $this->getLevel()->setBlock($this, Block::get($this->id, $meta));
	}
	public function isOnSlope(){
		$d = $this->meta;
		return ($d == 0x02 || $d == 0x03 || $d == 0x04 || $d == 0x05);
	}
	public function isCurve(){
		$d = $this->meta;
		return ($d == 0x06 || $d == 0x07 || $d == 0x08 || $d == 0x09);
	}
	public function getHardness(){
		return 0.1;
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent() === true){
				$this->getLevel()->useBreakOn($this);
				
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}
	public static function check($rail){
	    $array = [
	    [[0, 1], [0, -1]],
	    [[1, 0], [-1, 0]],
	    [[1, 0], [-1, 0]],
	    [[1, 0], [-1, 0]],
	    [[0, 1], [0, -1]],
	    [[0, 1], [0, -1]],
	    [[1, 0], [0, 1]],
	    [[0, 1], [-1, 0]],
	    [[-1, 0], [0, -1]],
	    [[0, -1], [1, 0]]
	    ];
	    $arrayY = [0, 1, -1];
	    $blocks = $array[$rail->getDamage()];
	    $connected = [];
	    foreach($arrayY as $y){
	        $v3 = new Vector3($rail->x + $blocks[0][0], $rail->y + $y, $rail->z + $blocks[0][1]);
	        $id = $rail->getLevel()->getBlockIdAt($v3->x, $v3->y, $v3->z);
	        $meta = $rail->getLevel()->getBlockDataAt($v3->x, $v3->y, $v3->z);
	        if(in_array($id, array(self::RAIL, self::POWERED_RAIL, self::ACTIVATOR_RAIL, self::DETECTOR_RAIL)) and in_array([$rail->x - $v3->x, $rail->z - $v3->z], $array[$meta])){
	            $connected[] = $v3;
	            break;
	        }
	    }
	    foreach($arrayY as $y){
	        $v3 = new Vector3($rail->x + $blocks[1][0], $rail->y + $y, $rail->z + $blocks[1][1]);
	        $id = $rail->getLevel()->getBlockIdAt($v3->x, $v3->y, $v3->z);
	        $meta = $rail->getLevel()->getBlockDataAt($v3->x, $v3->y, $v3->z);
	        if(in_array($id, array(self::RAIL, self::POWERED_RAIL, self::ACTIVATOR_RAIL, self::DETECTOR_RAIL)) and in_array([$rail->x - $v3->x, $rail->z - $v3->z], $array[$meta])){
	            $connected[] = $v3;
	            break;
	        }
	    }
	    return $connected;
	}
}