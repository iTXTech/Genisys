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
			"%pocketmine.command.weather.description",
			"%pocketmine.command.weather.usage"
		);
		$this->setPermission("pocketmine.command.weather");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		if($sender instanceof Player){
			$wea = (int) $args[0];
			if(!isset($args[1])) $duration = mt_rand(min($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax), max($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax));
			else $duration = (int) $args[1];
			if($wea >= 0 and $wea <= 3){
				if(WeatherManager::isRegistered($sender->getLevel())){
					$sender->getLevel()->getWeather()->setWeather($wea, $duration);
					$sender->sendMessage(new TranslationContainer("pocketmine.command.weather.changed", [$sender->getLevel()->getFolderName()]));
					return true;
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.weather.noregistered", [$sender->getLevel()->getFolderName()]));
					return false;
				}
			}else{
				$sender->sendMessage(TextFormat::RED . "%pocketmine.command.weather.invalid");
				return false;
			}
		}

		if(count($args) < 2){
			$sender->sendMessage(TextFormat::RED . "%pocketmine.command.weather.wrong");
			return false;
		}

		$level = $sender->getServer()->getLevelByName($args[0]);
		if(!$level instanceof Level){
			$sender->sendMessage(TextFormat::RED . "%pocketmine.command.weather.invalid.level");
			return false;
		}

		$wea = (int) $args[1];
		if(!isset($args[1])) $duration = mt_rand(min($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax), max($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax));
		else $duration = (int) $args[1];
		if($wea >= 0 and $wea <= 3){
			if(WeatherManager::isRegistered($level)){
				$level->getWeather()->setWeather($wea, $duration);
				$sender->sendMessage(new TranslationContainer("pocketmine.command.weather.changed", [$level->getFolderName()]));
				return true;
			}else{
				$sender->sendMessage(new TranslationContainer("pocketmine.command.weather.noregistered", [$level->getFolderName()]));
				return false;
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "%pocketmine.command.weather.invalid");
			return false;
		}
	}
}
