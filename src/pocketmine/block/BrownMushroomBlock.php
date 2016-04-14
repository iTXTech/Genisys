<?php


namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\enchantment\enchantment;
use pocketmine\Player;

class RedMushroomBlock extends Solid{

	const RED = 14;
	const STEM = 10;

	protected $id = self::RED_MUSHROOM_BLOCK;

	public function __construct($meta = 15){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 0.5;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getName() : string{
		return "Red Mushroom Block";
	}
	
	public function getDrops(Item $item) : array {
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::RED_MUSHROOM_BLOCK, SELF::RED, 1],
			];
		}else{
			return [
				[Item::RED_MUSHROOM, 0, mt_rand(0, 2)],
			];
		}
	}
}
