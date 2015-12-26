<?php
namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ExtractPluginCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"解包Phar插件",
			"/extractplugin <插件名称>"
		);
		$this->setPermission("pocketmine.command.extractplugin");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return false;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "用法: ".$this->usageMessage);
			return true;
		}

		$pluginName = trim(implode(" ", $args));
		if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)){
			$sender->sendMessage(TextFormat::RED . "插件名称错误，请检查。");
			return true;
		}
		$description = $plugin->getDescription();

		if(!($plugin->getPluginLoader() instanceof PharPluginLoader)){
			$sender->sendMessage(TextFormat::RED . "插件 ".$description->getName()." 不是一个Phar插件。");
			return true;
		}

		$folderPath = Server::getInstance()->getPluginPath().DIRECTORY_SEPARATOR . "PocketMine-iTX" . DIRECTORY_SEPARATOR . $description->getName()."_v".$description->getVersion()."/";
		if(file_exists($folderPath)){
			$sender->sendMessage("插件已存在，正在覆盖。");
		}else{
			@mkdir($folderPath);
		}

		$reflection = new \ReflectionClass("pocketmine\\plugin\\PluginBase");
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$pharPath = str_replace("\\", "/", rtrim($file->getValue($plugin), "\\/"));

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		$sender->sendMessage("插件源码 ".$description->getName() ." v".$description->getVersion()." 已经被创建到 ".$folderPath);
		return true;
	}
}