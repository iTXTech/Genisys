<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/1/10 21:30
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 *
 * Copied from Weaather_boybook
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
				$biome = isset($args[1]) ? $args[1] : 0;
				if(isset($sender->selectedPos[0]) and isset($sender->selectedPos[1])){
					$x1 = min($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z1 = min($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					$x2 = max($sender->selectedPos[0][0], $sender->selectedPos[1][0]);
					$z2 = max($sender->selectedPos[0][1], $sender->selectedPos[1][1]);
					for($x = $x1; $x <= $x2; $x++){
						for($z = $z1; $z <= $z2; $z++){
							$level = $sender->getLevel();
							$level->setBiomeId($x, $z, $biome);
						}
					}
					$sender->sendMessage(TextFormat::GREEN . "已成功设置生态为 $biome");
				}else{
					$sender->sendMessage("请先通过 /biome pos1/pos2 设定范围");
				}
			}elseif($args[0] == "color"){
				$color = isset($args[1]) ? $args[1] : "130,180,147";
				$a = explode(",", $color);
				var_dump($a);
				if(count($a) != 3) return false;
				if(isset($sender->selectedPos[0]) and isset($sender->selectedPos[1])){
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
					$sender->sendMessage(TextFormat::GREEN . "已成功设置生态颜色为 $a[0], $a[1], $a[2]");
				}else{
					$sender->sendMessage("请先通过 /biome pos1/pos2 设定范围");
				}
			}elseif($args[0] == "pos1"){
				$x = $sender->getX();
				$z = $sender->getZ();
				$sender->selectedPos[0][0] = $x;
				$sender->selectedPos[0][1] = $z;
				$sender->sendMessage("已设置第一个坐标为 $x, $z");
			}elseif($args[0] == "pos2"){
				$x = $sender->getX();
				$z = $sender->getZ();
				$sender->selectedPos[1][0] = $x;
				$sender->selectedPos[1][1] = $z;
				$sender->sendMessage("已设置第二个坐标为 $x, $z");
			}elseif($args[0] == "get"){
				$x = $sender->getX();
				$z = $sender->getZ();
				$biome = $sender->getLevel()->getBiomeId($x, $z);
				$color = $sender->getLevel()->getBiomeColor($x, $z);
				$sender->sendMessage("您所在的生态id为: $biome");
			}
		}

		return true;
	}
}