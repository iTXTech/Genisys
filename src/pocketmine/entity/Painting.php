<?php
/*
 * This file is translated from the Nukkit Project
 * which is written by MagicDroidX
 * @link https://github.com/Nukkit/Nukkit
*/

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\protocol\AddPaintingPacket;
use pocketmine\item\Item as ItemItem;
use pocketmine\Player;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\block\Block;

class Painting extends Hanging{
	const NETWORK_ID = 83;

	private $motive;

	public function initEntity(){
		$this->setMaxHealth(1);
		parent::initEntity();

		if(isset($this->namedtag->Motive)){
			$this->motive = $this->namedtag["Motive"];
		}else $this->close();
	}

	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
		if($source->isCancelled()) return false;
		$this->level->addParticle(new DestroyBlockParticle($this->add(0.5), Block::get(Block::LADDER)));
		$this->kill();
	}

	public function spawnTo(Player $player){
		$pk = new AddPaintingPacket();
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->direction = $this->getDirection();
		$pk->title = $this->motive;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	protected function updateMovement(){
		//Nothing to update, paintings cannot move.
	}

	public function getDrops(){
		return [ItemItem::get(ItemItem::PAINTING, 0, 1)];
	}
}