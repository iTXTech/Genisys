<?php


namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\enchantment\enchantment;
use pocketmine\Player;

class BrownMushroomBlock extends Solid{
	
	const BROWN = 14;

	protected $id = self::BROWN_MUSHROOM_BLOCK;

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
		return "Brown Mushroom Block";
	}
	
	public function getDrops(Item $item) : array {
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::BROWN_MUSHROOM_BLOCK, SELF::BROWN, 1],
			];
		}else{
			return [
				[Item::BROWN_MUSHROOM, 0, mt_rand(0, 2)],
			];
		}
	}
}
