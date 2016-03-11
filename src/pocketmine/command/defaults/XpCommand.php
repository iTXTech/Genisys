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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class XpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.xp.description",
			"%commands.xp.usage"
		);
		$this->setPermission("pocketmine.command.xp");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) != 2){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}else{
			$player = $sender->getServer()->getPlayerExact($name = $args[1]);
			if($player instanceof Player){
				if(strcasecmp(substr($args[0], -1), "L") == 0){			//Set Experience Level(with "L" after args[0])
					$level = rtrim($args[0], "Ll");
					if(is_numeric($level)){
						$player->addExpLevel($level);
						$sender->sendMessage("Successfully add $level Level of experience to $name");
					}
				}elseif(is_numeric($args[0])){											//Set Experience
					$player->addExperience($args[0]);
					$sender->sendMessage("Successfully add $args[0] of experience to $name");
				}else{
					$sender->sendMessage("Argument error.");
					return false;
				}
			}else{
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
				return false;
			}
		}
		return false;
	}
}
