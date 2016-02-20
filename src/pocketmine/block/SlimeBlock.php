<?php


namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;

class SlimeBlock extends Solid{

	protected $id = self::SLIME_BLOCK;

	public function __construct($meta = 15){
		$this->meta = $meta;
	}

	public function getHardness() {
		return 0.5;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getName() : string{
		return "Slime Block";
	}
}
