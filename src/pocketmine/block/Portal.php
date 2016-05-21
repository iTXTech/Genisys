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
 * @link https://mcper.cn
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\particle\PortalParticle;
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

	public function canBeActivated() : bool {
		return true;
	}

	public function hasEntityCollision(){
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			for($n = 0;$n <= 2;$n++){
				$sound = new EndermanTeleportSound($this);
				$this->getLevel()->addSound($sound);
			}
			
			for($num = 0;$num <= 10;$num++){
				$particle = new PortalParticle($this);
				$this->getLevel()->addParticle($particle);
			}
		}

		return true;
	}

	public function onBreak(Item $item) {
		$sound = new EndermanTeleportSound($this);
		$this->getLevel()->addSound($sound);
		$particle = new PortalParticle($this);
		$this->getLevel()->addParticle($particle);
		$block = $this;
		//$this->getLevel()->setBlock($block, new Block(Block::PORTAL, 0));//在破坏处放置一个方块防止计算出错
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
			$this->meta = ((int) $player->getDirection() + 5) % 2;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}
	
	public function getDrops(Item $item) : array {
		if($item->isPickaxe() >= 1){
			return [
				[Item::PORTAL, 0, 1],
			];
		}else{
			return [];
		}
	}
}