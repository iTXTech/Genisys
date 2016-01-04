<?php

namespace pocketmine\item;

use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\entity\Boat as BoatEntity;

class Boat extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::BOAT, $meta, $count, "Boat");
	}

	public function canBeActivated(){
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$realPos = $block->getSide($face);

		$boat = new BoatEntity($player->getLevel()->getChunk($realPos->getX() >> 4, $realPos->getZ() >> 4), new Compound("", [
			"Pos" => new Enum("Pos", [
				new Double("", $realPos->getX()),
				new Double("", $realPos->getY()),
				new Double("", $realPos->getZ())
			]),
			"Motion" => new Enum("Motion", [
				new Double("", 0),
				new Double("", 0),
				new Double("", 0)
			]),
			"Rotation" => new Enum("Rotation", [
				new Float("", 0),
				new Float("", 0)
			]),
		]));
		$boat->spawnToAll();

		if($player->isSurvival()) {
			$item = $player->getInventory()->getItemInHand();
			$count = $item->getCount();
			if(--$count <= 0){
				$player->getInventory()->setItemInHand(Item::get(Item::AIR));
				return true;
			}

			$item->setCount($count);
			$player->getInventory()->setItemInHand($item);
		}
		
		return true;
	}
}
