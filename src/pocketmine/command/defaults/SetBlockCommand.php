<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;
use pocketmine\level\Level;

class SetBlockCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.setblock.description",
			"%commands.setblock.usage"
		);
		$this->setPermission("pocketmine.command.setblock");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 4 or count($args) > 5){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}

		$itemblock = Item::fromString($args[3]);
		if($itemblock instanceof ItemBlock){
			$block = $itemblock->getBlock();
			if(isset($args[4]) and is_numeric($args[4])) $block->setDamage((int)$args[4]);

			$x = $args[0];
			$y = $args[1];
			$z = $args[2];

			if($x{0} === "~"){
				if((is_numeric(trim($x, "~")) or trim($x, "~") === "") and ($sender instanceof Player)) $x = (int)round(trim($x, "~") + $sender->x);
			}elseif(is_numeric($x)){
				$x = (int)round($x);
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
			}
			if($y{0} === "~"){
				if((is_numeric(trim($y, "~")) or trim($y, "~") === "") and ($sender instanceof Player)) $y = (int)round(trim($y, "~") + $sender->y);
				if($y < 0 or $y > 128) return false;
			}elseif(is_numeric($y)){
				$y = (int)round($y);
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
			}
			if($z{0} === "~"){
				if((is_numeric(trim($z, "~")) or trim($z, "~") === "") and ($sender instanceof Player)) $z = (int)round(trim($z, "~") + $sender->z);
			}elseif(is_numeric($z)){
				$z = (int)round($z);
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
			}
			if(!(is_integer($x) and is_integer($y) and is_integer($z))){
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return false;
			}

			$pos = new Vector3($x, $y, $z);
			if($pos instanceof Vector3){
				$level = ($sender instanceof Player) ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();
				if($level->setBlock($pos, $block)){
					$sender->sendMessage("Successfully set the block at ($x, $y, $z) to block $args[3]");
					return true;
				}else{
					$sender->sendMessage(TextFormat::RED . new TranslationContainer("commands.generic.exception", []));
					return false;
				}
			}
		}else{
			$sender->sendMessage(TextFormat::RED . new TranslationContainer("command.setblock.invalidBlock", []));
			return false;
		}
		return true;
	}
}
