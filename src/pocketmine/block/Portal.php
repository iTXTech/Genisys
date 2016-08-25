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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;
use pocketmine\math\Vector3;

class Portal extends Transparent{

	protected $id = self::PORTAL;

	/** @var  Vector3 */
	private $temporalVector = null;
	
	public function __construct(){
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}

	public function getName() : string{
		return "Portal";
	}
	
	public function getHardness() {
		return -1;
	}

	public function getResistance(){
		return 0;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function canPassThrough(){
		return true;
	}

	public function hasEntityCollision(){
		return true;
	}

	public function onBreak(Item $item){
		$block = $this;
		if($this->getLevel()->getBlock($this->temporalVector->setComponents($block->x - 1, $block->y, $block->z))->getId() == Block::PORTAL or
			$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x + 1, $block->y, $block->z))->getId() == Block::PORTAL){//x方向
			for($x = $block->x;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() == Block::PORTAL;$x++){
				for($y = $block->y;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL;$y++){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
				}
				for($y = $block->y - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL;$y--){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
				}
			}
			for($x = $block->x - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() == Block::PORTAL;$x--){
				for($y = $block->y;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL;$y++){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
				}
				for($y = $block->y - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL;$y--){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
				}
			}
		}else{//z方向
			for($z = $block->z;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() == Block::PORTAL;$z++){
				for($y = $block->y;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL;$y++){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
				}
				for($y = $block->y - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL;$y--){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
				}
			}
			for($z = $block->z - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() == Block::PORTAL;$z--){
				for($y = $block->y;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL;$y++){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
				}
				for($y = $block->y - 1;$this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL;$y--){
					$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
				}
			}
		}
		parent::onBreak($item);
	}
	
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = $player->getDirection() & 0x01;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}
	
	public function getDrops(Item $item) : array {
		return [];
	}
}