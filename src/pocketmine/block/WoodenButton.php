<?php
/**
 * Author: PeratX
 * Time: 2015/12/20 18:47
 * Copyright(C) 2011-2015 iTX Technologies LLC.
 * All rights reserved.
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\sound\ButtonClickSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class WoodenButton extends RedstoneSource{
	protected $id = self::WOODEN_BUTTON;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->isActivated()) {
				$this->meta ^= 0x08;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->getLevel()->addSound(new ButtonClickSound($this));
				$this->deactivate();
			}
			return Level::BLOCK_UPDATE_SCHEDULED;
		}
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$side = $this->getDamage();
			if($this->isActivated()) $side ^= 0x08;
			$faces = [
				0 => 1,
				1 => 0,
				2 => 3,
				3 => 2,
				4 => 5,
				5 => 4,
			];

			if($this->getSide($faces[$side]) instanceof Transparent){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function deactivate(array $ignore = []){
		parent::deactivate($ignore = []);
		$faces = [
			0 => 1,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;

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

		if($side != 1){
			/** @var Door $block */
			$block = $this->getSide($faces[$side], 2);
			if(!$this->checkPower($block)){
				if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
					if($block->isOpened()) $block->onActivate(new Item(0));
				}
				/** @var ActiveRedstoneLamp $block */
				if($block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOff();
			}
			if($block->getId() == Block::REDSTONE_WIRE) {
				/** @var RedstoneWire $wire */
				$wire = $block;
				$wire->calcSignal(0, RedstoneWire::OFF);
			}
		}

		$this->checkTorchOff($this->getSide($faces[$side]),[$this->getOppositeSide($faces[$side])]);
	}

	public function activate(array $ignore = []){
		parent::activate($ignore = []);
		$faces = [
				1 => 0,
				2 => 3,
				3 => 2,
				4 => 5,
				5 => 4,
		];
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->isRightPlace($this, $block)){
			if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
				if(!$block->isOpened()) $block->onActivate(new Item(0));
			}
			if($block->getId() == Block::TNT) $block->onActivate(new Item(Item::FLINT_AND_STEEL));
			/** @var ActiveRedstoneLamp $block */
			if($block->getId() == Block::INACTIVE_REDSTONE_LAMP or $block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOn();
			if($block->getId() == Block::REDSTONE_WIRE){
				/** @var RedstoneWire $wire */
				$wire = $block;
				$wire->calcSignal($this->maxStrength, RedstoneWire::ON);
			}
		}

		if($side != 1){
			/** @var Door $block */
			$block = $this->getSide($faces[$side], 2);
			if(($block instanceof Door) or ($block instanceof Trapdoor) or ($block instanceof FenceGate)){
				if(!$block->isOpened()) $block->onActivate(new Item(0));
			}
			if($block->getId() == Block::TNT) $block->onActivate(new Item(Item::FLINT_AND_STEEL));
			/** @var ActiveRedstoneLamp $block */
			if($block->getId() == Block::INACTIVE_REDSTONE_LAMP or $block->getId() == Block::ACTIVE_REDSTONE_LAMP) $block->turnOn();
			if($block->getId() == Block::REDSTONE_WIRE) {
				/** @var RedstoneWire $wire */
				$wire = $block;
				$wire->calcSignal(15, RedstoneWire::ON);
			}
		}

		$this->checkTorchOn($this->getSide($faces[$side]),[$this->getOppositeSide($faces[$side])]);
	}

	public function getName(){
		return "Wooden Button";
	}

	public function getHardness(){
		return 0.5;
	}

	public function onBreak(Item $item){
		if($this->isActivated()){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->deactivate();
		}
		$this->getLevel()->setBlock($this, new Air(), true, false);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$this->meta = $face;
			$this->getLevel()->setBlock($block, $this, true, false);
			return true;
		}
		return false;
	}

	public function canBeActivated(){
		return true;
	}

	public function isActivated(){
		return (($this->meta & 0x08) === 0x08);
	}

	public function onActivate(Item $item, Player $player = null){
		if(!$this->isActivated()){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->getLevel()->addSound(new ButtonClickSound($this));
			$this->activate();
			$this->getLevel()->scheduleUpdate($this, $this->getLevel()->getServer()->getTicksPerSecondAverage() * 2);
		}
		return true;
	}
}