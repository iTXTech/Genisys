<?php
namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ExtractPharCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"解包Phar",
			"/extractphar <Phar文件名>"
		);
		$this->setPermission("pocketmine.command.extractphar");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "用法: ".$this->usageMessage);
			return true;
		}
		if(!isset($args[0]) or !file_exists($args[0])) return \false;
		$folderPath = $sender->getServer()->getPluginPath().DIRECTORY_SEPARATOR . "PocketMine-iTX" . DIRECTORY_SEPARATOR . basename($args[0]);
		if(file_exists($folderPath)){
			$sender->sendMessage("文件夹已存在，正在覆盖。");
		}else{
			@mkdir($folderPath);
		}
		
		$pharPath = "phar://$args[0]";

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			$sender->sendMessage("[PocketMine-iTX] 正在解包 $path");
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		$sender->sendMessage("Phar归档 $args[0] 已被成功解包到 $folderPath");
	}
}