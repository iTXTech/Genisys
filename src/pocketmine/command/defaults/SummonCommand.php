<?php
/**
 * Author: gaoyichuan
 * Time: 2016/2/2 15:16
 *
 * OpenGenisys Project
 */


namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\utils\TextFormat;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

class SummonCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.summon.description",
			"%commands.summon.usage"
		);
		$this->setPermission("pocketmine.command.summon");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) != 1 and count($args) != 4 and count($args) != 5){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return true;
		}

		$x = 0;
		$y = 0;
		$z = 0;
		if(count($args) == 4 or count($args) == 5){			//position is set
			//TODO:simpilify them to one piece of code
			//Code for setting $x
			if(is_numeric($args[1])){							//x is given directly
				$x = $args[1];
			}elseif(strcmp($args[1], "~") >= 0){	//x is given with a "~"
				$offset_x = trim($args[1], "~");
				if($sender instanceof Player){			//using in-game
					$x = is_numeric($offset_x) ? ($sender->x + $offset_x) : $sender->x;
				}else{															//using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{																//other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}

			//Code for setting $y
			if(is_numeric($args[2])){							//y is given directly
				$y = $args[2];
			}elseif(strcmp($args[2], "~") >= 0){	//y is given with a "~"
				$offset_y = trim($args[2], "~");
				if($sender instanceof Player){			//using in-game
					$y = is_numeric($offset_y) ? ($sender->y + $offset_y) : $sender->y;
					if($y < 0) $y = 0;								//in case y is outside the range of [0,128]
					if($y > 128) $y = 128;
				}else{															//using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{																//other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}

			//Code for setting $z
			if(is_numeric($args[3])){							//z is given directly
				$z = $args[3];
			}elseif(strcmp($args[3], "~") >= 0){	//z is given with a "~"
				$offset_z = trim($args[3], "~");
				if($sender instanceof Player){			//using in-game
					$z = is_numeric($offset_z) ? ($sender->z + $offset_z) : $sender->z;
				}else{															//using in console
					$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
					return false;
				}
			}else{																//other circumstances
				$sender->sendMessage(TextFormat::RED . "Argument error");
				return false;
			}
		}	//finish setting the location

		if(count($args) == 1){
			if($sender instanceof Player){
				$x = $sender->x;
				$y = $sender->y;
				$z = $sender->z;
			}else{
				$sender->sendMessage(TextFormat::RED . "You must specify a position where the entity is spawned to when using in console");
				return false;
			}
		} //finish setting the location

		$entity = null;
		$type = $args[0];
		$level = ($sender instanceof Player) ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();
		$chunk = $level->getChunk(round($x) >> 4, round($z) >> 4);
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $x),
				new DoubleTag("", $y),
				new DoubleTag("", $z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
		]);

		$entity = Entity::createEntity($type, $chunk, $nbt);
		if($entity instanceof Entity){
			$entity->spawnToAll();
			$sender->sendMessage("Successfully spawned entity $type at ($x, $y, $z)");
			return true;
		}else{
			$sender->sendMessage(TextFormat::RED . "An error occurred when spawning the entity $type");
			return false;
		}
	}
}
