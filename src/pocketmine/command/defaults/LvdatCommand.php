<?php

/**
 * Author: PeratX
 * OpenGenisys Project
 */

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;

use pocketmine\level\format\generic\BaseLevelProvider;
use pocketmine\level\generator\Generator;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Compound;
use pocketmine\math\Vector3;

class LvdatCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Change properties of a map",
			"/lvdat <level-name> <opts|help>"
		);
		$this->setPermission("pocketmine.command.lvdat");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}
		if(!$this->autoLoad($sender, $args[0])) return false;
		else{
			$level = $sender->getServer()->getLevelByName($args[0]);
			if(!$level) return false;
			/** @var BaseLevelProvider $provider */
			$provider = $level->getProvider();
			if(!isset($args[1])) return false;
			$p = 0;
			if(strstr($args[1], "=")){
				$opt = explode("=", $args[1]);
				list($o, $p) = $opt;
			}else $o = $args[1];
			if($o == "seed"){
				$provider->setSeed($p);
				$sender->sendMessage("对世界 " . $level->getFolderName() . " 的修改已保存，部分属性可能需要重启服务器后生效。");
			}elseif($o == "name"){
				$provider->getLevelData()->LevelName = new String("LevelName", $p);
				$sender->sendMessage("对世界 " . $level->getFolderName() . " 的修改已保存，部分属性可能需要重启服务器后生效。");
			}elseif($o == "generator"){
				$provider->getLevelData()->generatorName = new String("generatorName", $p);
				$sender->sendMessage("对世界 " . $level->getFolderName() . " 的修改已保存，部分属性可能需要重启服务器后生效。");
			}elseif($o == "preset"){
				$provider->getLevelData()->generatorOptions = new String("generatorOptions", $p);
				$sender->sendMessage("对世界 " . $level->getFolderName() . " 的修改已保存，部分属性可能需要重启服务器后生效。");
			}elseif($args[1] == "fixname"){
				$provider->getLevelData()->LevelName = new String("LevelName", $level->getFolderName());
				$sender->sendMessage("对世界 " . $level->getFolderName() . " 的修改已保存，部分属性可能需要重启服务器后生效。");
			}elseif($args[1] == "help"){
				$sender->sendMessage("/lvdat用法");
				$sender->sendMessage("/lvdat 地图文件夹名称 fixname");
				$sender->sendMessage("/lvdat 地图文件夹名称 seed=种子");
				$sender->sendMessage("/lvdat 地图文件夹名称 name=名称");
				$sender->sendMessage("/lvdat 地图文件夹名称 generator=生成器名称");
				$sender->sendMessage("/lvdat 地图文件夹名称 preset=生成器选项（预设）");
			}else return false;
			$provider->saveLevelData();
		}
		return false;
	}

	public function autoLoad(CommandSender $c, $world){
		if($c->getServer()->isLevelLoaded($world)) return true;
		if(!$c->getServer()->isLevelGenerated($world)){
			return false;
		}
		$c->getServer()->loadLevel($world);
		return $c->getServer()->isLevelLoaded($world);
	}
}