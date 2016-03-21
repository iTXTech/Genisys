<?php
/**
 * Author: PeratX
 * OpenGenisys Project
 */
namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ChunkInfoCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"Gets the information of a chunk",
			"/chunkinfo (x) (y) (z) (levelName)"
		);
		$this->setPermission("pocketmine.command.chunkinfo");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(!$sender instanceof Player and count($args) < 4){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		if($sender instanceof Player){
			$pos = $sender->getPosition();
		}else{
			$level = $sender->getServer()->getLevelByName($args[3]);
			if(!$level instanceof Level){
				$sender->sendMessage(TextFormat::RED . "Invalid level name");

				return false;
			}
			$pos = new Position((int) $args[0], (int) $args[1], (int) $args[2], $level);
		}

		$chunk = $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4);
		McRegion::getRegionIndex($chunk->getX(), $chunk->getZ(), $x, $z);

		$sender->sendMessage("Region X: $x Region Z: $z");

		return true;
	}
}