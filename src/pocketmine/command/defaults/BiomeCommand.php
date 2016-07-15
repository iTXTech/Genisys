<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class BiomeCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.biome.description",
			"/biome <pos1|pos2|get|set|color>"
		);
		$this->setPermission("pocketmine.command.biome");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}

		if($sender instanceof Player){
			if($args[0] == "set"){
				$biome = isset($args[1]) ? $args[1] : 1;//默认改成草原
				if(isset($sender->selectedPos[0]) and isset($sender->selectedPos[1])){
					if(is_numeric($biome) === false){
						$sender->sendMessage(TextFormat::RED . new TranslationContainer("pocketmine.command.biome.wrongBio"));
						return false;
					}
					$biome = (int) $biome;
					if($sender->selectedLev[0] !== $sender->selectedLev[1]){
						$sender->sendMessage(TextFormat::RED . new TranslationContainer("pocketmine.command.biome.wrongLev"));
						return false;
					}
					$x1 = min($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z1 = min($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					$x2 = max($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z2 = max($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					$level = $sender->selectedLev[0];
					for($x = $x1; $x <= $x2; $x++){
						for($z = $z1; $z <= $z2; $z++){
							$level->setBiomeId($x, $z, $biome);
						}
					}
					$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.set", [$biome]));
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.noPos"));
				}
			}elseif($args[0] == "color"){
				$color = isset($args[1]) ? $args[1] : "146,188,89";//1=草原("146,188,89"),2=沙漠(251,183,19)"130,180,147"
				$a = explode(",", $color);
				//var_dump($a);
				if(count($a) != 3){
					$sender->sendMessage(TextFormat::RED . new TranslationContainer("pocketmine.command.biome.wrongCol"));
					return false;
				}
				if(isset($sender->selectedPos[0]) and isset($sender->selectedPos[1])){
					if($sender->selectedLev[0] !== $sender->selectedLev[1]){
						$sender->sendMessage(TextFormat::RED . new TranslationContainer("pocketmine.command.biome.wrongLev"));
						return false;
					}
					$x1 = min($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z1 = min($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					$x2 = max($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z2 = max($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					for($x = $x1; $x <= $x2; $x++){
						for($z = $z1; $z <= $z2; $z++){
							$level = $sender->getLevel();
							$level->setBiomeColor($x, $z, $a[0], $a[1], $a[2]);
						}
					}
					//$sender->selectedPos = array();
					$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.color", [$a[0], $a[1], $a[2]]));
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.noPos"));
				}
			}elseif($args[0] == "pos1"){
				$x = floor($sender->getX());
				$z = floor($sender->getZ());
				$sender->selectedLev[0] = $sender->getLevel();
				$sender->selectedPos[0][0] = $x;
				$sender->selectedPos[0][1] = $z;
				$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.posset", [$sender->selectedLev[0]->getName(), $x, $z, "1"]));
			}elseif($args[0] == "pos2"){
				$x = floor($sender->getX());
				$z = floor($sender->getZ());
				$sender->selectedLev[1] = $sender->getLevel();
				$sender->selectedPos[1][0] = $x;
				$sender->selectedPos[1][1] = $z;
				$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.posset", [$sender->selectedLev[1]->getName(), $x, $z, "2"]));
			}elseif($args[0] == "get"){
				$x = floor($sender->getX());
				$z = floor($sender->getZ());
				$biome = $sender->getLevel()->getBiomeId($x, $z);
				$color = $sender->getLevel()->getBiomeColor($x, $z);
				$sender->sendMessage(new TranslationContainer("pocketmine.command.biome.get", [$biome, $color[0], $color[1], $color[2]]));
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
				return true;
			}
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.runingame"));
			return false;
		}
		return true;
	}
}
