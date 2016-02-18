<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/2 11:23


 *
 * OpenGenisys Project
 */
namespace pocketmine\item;

class EnchantedBook extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::ENCHANTED_BOOK, $meta, $count, "Enchanted Book");
	}
}