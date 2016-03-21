<?php
/**
 * Author: PeratX
 * Time: 2015/12/11 17:29
 ]

 */

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;
use pocketmine\Player;

class PressurePlate extends RedstoneSource{
	protected $activateTime = 0;
	protected $canActivate = true;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function hasEntityCollision(){
		return true;
	}

	public function onEntityCollide(Entity $entity){
		if($this->getLevel()->getServer()->redstoneEnabled and $this->canActivate){
			if(!$this->isActivated() or ($this->isActivated() and ($this->getLevel()->getServer()->getTick() % ($this->getLevel()->getServer()->getTicksPerSecondAverage() * 1.5)) == 0)){
				$this->meta = 1;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->activate();
				$this->getLevel()->addSound(new GenericSound($this, 1000));
				//$this->activateTime = $this->getLevel()->getServer()->getTick();
				$this->getLevel()->scheduleUpdate($this, $this->getLevel()->getServer()->getTicksPerSecondAverage() * 1.5);
			}
		}
	}

	public function isActivated(){
		return ($this->meta == 0) ? false : true;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			$below = $this->getSide(Vector3::SIDE_DOWN);
			if($below instanceof Transparent){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		/*if($type == Level::BLOCK_UPDATE_SCHEDULED){
			if($this->isActivated()){
				if(!$this->isCollided()){
					$this->meta = 0;
					$this->getLevel()->setBlock($this, $this, true, false);
					$this->deactivate();
					return Level::BLOCK_UPDATE_SCHEDULED;
				}
			}
		}*/
		return true;
	}

	public function checkActivation(){
		if($this->isActivated()){
			if((($this->getLevel()->getServer()->getTick() - $this->activateTime)) >= 3){
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->deactivate();
			}
		}
	}

	/*public function isCollided(){
		foreach($this->getLevel()->getEntities() as $p){
			$blocks = $p->getBlocksAround();
			if(isset($blocks[Level::blockHash($this->x, $this->y, $this->z)])) return true;
		}
		return false;
	}*/

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$below = $this->getSide(Vector3::SIDE_DOWN);
		if($below instanceof Transparent) return;
		else $this->getLevel()->setBlock($block, $this, true, false);
	}

	public function onBreak(Item $item){
		if($this->isActivated()){
			$this->meta = 0;
			$this->deactivate();
		}
		$this->canActivate = false;
		$this->getLevel()->setBlock($this, new Air(), true);
	}
}