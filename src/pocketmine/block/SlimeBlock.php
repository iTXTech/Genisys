<?php


namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class SlimeBlock extends Solid{

	protected $id = self::SLIME_BLOCK;

	public function __construct($meta = 15){
		$this->meta = $meta;
	}

	public function getHardness() {
		return 0;
	}

	public function getName() : string{
		return "Slime Block";
	}
}
