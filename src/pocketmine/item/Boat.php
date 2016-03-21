<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\item;

use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\nbt\tag\IntTag;
use pocketmine\Player;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\entity\Boat as BoatEntity;

class Boat extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::BOAT, $meta, $count, "Boat");
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$realPos = $block->getSide($face);

		$boat = new BoatEntity($player->getLevel()->getChunk($realPos->getX() >> 4, $realPos->getZ() >> 4), new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $realPos->getX() + 0.5),
				new DoubleTag("", $realPos->getY()),
				new DoubleTag("", $realPos->getZ() + 0.5)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", 0),
				new FloatTag("", 0)
			]),
			"WoodID" => new IntTag("WoodID", $this->getDamage())
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
