<?php
namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\level\weather\WeatherManager;

class WeatherCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"设置天气",
			"/weather <level-name> <weather>"
		);
		$this->setPermission("pocketmine.command.weather");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}
		
		if($sender instanceof Player){
			$wea = (int) $args[0];
			if($wea >= 0 and $wea <= 3){
				if(WeatherManager::isRegistered($sender->getLevel())) {
					$sender->getLevel()->getWeather()->setWeather($wea);
					$sender->sendMessage(TextFormat::GREEN."天气设置成功！");
					return true;
				} else {
					$sender->sendMessage(TextFormat::RED."这个世界没有注册到天气管理器！");
					return false;
				}
			} else {
				$sender->sendMessage(TextFormat::RED."无效的天气！");
				return false;
			}
		}
		
		if(count($args) < 2) {
			$sender->sendMessage(TextFormat::RED."缺少参数！");
			return false;
		}
		
		$level = $sender->getServer()->getLevelByName($args[0]);
		if(!$level instanceof Level) {
			$sender->sendMessage(TextFormat::RED."错误的地图名！");
			return false;
		}
		
		$wea = (int) $args[1];
		if($wea >= 0 and $wea <= 3){
			if(WeatherManager::isRegistered($level)) {
				$level->getWeather()->setWeather($wea);
				$sender->sendMessage(TextFormat::GREEN."天气设置成功！");
				return true;
			} else {
				$sender->sendMessage(TextFormat::RED."这个世界没有注册到天气管理器！");
				return false;
			}
		} else {
			$sender->sendMessage(TextFormat::RED."无效的天气！");
			return false;
		}
		
		return true;
	}
}