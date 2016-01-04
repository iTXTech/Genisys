<?php
namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;

class PardonCidCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.unban.cid.description",
			"%commands.unbancid.usage"
		);
		$this->setPermission("pocketmine.command.pardoncid");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return \true;
		}

		if(\count($args) !== 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return \false;
		}

		$sender->getServer()->getCIDBans()->remove($args[0]);

		Command::broadcastCommandMessage($sender, new TranslationContainer("commands.unban.success", [$args[0]]));

		return \true;
	}
}
