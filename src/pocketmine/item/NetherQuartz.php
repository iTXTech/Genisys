<?php
namespace pocketmine\item;

class NetherQuartz extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::NETHER_QUARTZ, 0, $count, "Nether Quartz");
	}

} 
