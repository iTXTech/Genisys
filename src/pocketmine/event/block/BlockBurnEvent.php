<?php
/**
 * Author: PeratX
 * OpenGenisys Project
 */
namespace pocketmine\event\block;

use pocketmine\event\Cancellable;

class BlockBurnEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;
}