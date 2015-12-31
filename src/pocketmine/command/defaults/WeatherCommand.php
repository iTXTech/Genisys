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
			"%commands.bancid.usage"
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
			$wea = (int)$args[0];
			if($wea >= 0 and $wea <= 3){
				if(WeatherManager::isRegistered($sender->getLevel())){
					$sender->getLevel()->getWeather()->setWeather($wea);
					$sender->sendMessage(TextFormat::GREEN . "%pocketmine.command.weather.changed" . $sender->getLevel()->getFolderName());
					return true;
				}else{
					$sender->sendMessage(TextFormat::RED . $sender->getLevel()->getFolderName() . "%pocketmine.command.weather.noregistered");
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

		$wea = (int)$args[1];
		if($wea >= 0 and $wea <= 3){
			if(WeatherManager::isRegistered($level)){
				$level->getWeather()->setWeather($wea);
				$sender->sendMessage(TextFormat::GREEN . "%pocketmine.command.weather.changed" . $level->getFolderName());
				return true;
			}else{
				$sender->sendMessage(TextFormat::RED . $level->getFolderName() . "%pocketmine.command.weather.noregistered");
				return false;
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "%pocketmine.command.weather.invalid");
			return false;
		}

		return true;
	}
}
