<?php
/**
 * Author: PeratX
 * Time: 2015/12/20 21:07
 ]

 *
 * OpenGenisys Project
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Lever extends RedstoneSource{
	protected $id = self::LEVER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getName() : string{
		return "Lever";
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$side = $this->getDamage();
			if($this->isActivated()) $side ^= 0x08;
			$faces = [
				5 => 0,
				6 => 0,
				3 => 2,
				1 => 4,
				4 => 3,
				2 => 5,
				0 => 1,
				7 => 1,
			];

			$block = $this->getSide($faces[$side]);
			if($block->isTransparent()){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$faces = [
				3 => 3,
				2 => 4,
				4 => 2,
				5 => 1,
			];
			if($face === 0){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 0 : 7);
			}elseif($face === 1){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 6 : 5);
			}else{
				$this->meta = $faces[$face];
			}
			$this->getLevel()->setBlock($block, $this, true, false);
			return true;
		}
		return false;
	}

	public function activate(array $ignore = []){
		parent::activate($ignore);
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
				5 => 0,
				6 => 0,
				3 => 2,
				1 => 4,
				4 => 3,
				2 => 5,
				0 => 1,
				7 => 1,
		];

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->isRightPlace($this, $block)){
			$this->activateBlock($block);
		}

		$this->checkTorchOn($this->getSide($faces[$side]),[$this->getOppositeSide($faces[$side])]);
	}

	public function deactivate(array $ignore = []){
		parent::deactivate($ignore);
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
				5 => 0,
				6 => 0,
				3 => 2,
				1 => 4,
				4 => 3,
				2 => 5,
				0 => 1,
				7 => 1,
		];

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->isRightPlace($this, $block)){
			if(!$this->checkPower($block)){
				if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
					if($block->isOpened()) $block->onActivate(new Item(0));
				}
				/** @var ActiveRedstoneLamp $block */
				if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
			}
			if($block->getId() == Block::REDSTONE_WIRE){
				/** @var RedstoneWire $wire */
				$wire = $block;
				$wire->calcSignal(0, RedstoneWire::OFF);
			}
		}

		$this->checkTorchOff($this->getSide($faces[$side]),[$this->getOppositeSide($faces[$side])]);
	}

	public function onActivate(Item $item, Player $player = null){
		$this->meta ^= 0x08;
		$this->getLevel()->setBlock($this, $this, true, false);
		if($this->isActivated()) $this->activate();
		else $this->deactivate();
		return true;
	}

	public function onBreak(Item $item){
		if($this->isActivated()){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->deactivate();
		}
		$this->getLevel()->setBlock($this, new Air(), true, false);
	}

	public function isActivated(){
		return (($this->meta & 0x08) === 0x08);
	}

	public function getDrops(Item $item) : array {
		return [
			[$this->id, 0 ,1],
		];
	}
}