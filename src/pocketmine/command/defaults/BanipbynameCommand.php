<?php
namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BanipbynameCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"用用户名ban掉熊孩子的ip",
			"/banipbyname <player>"
		);
		$this->setPermission("pocketmine.command.banipbyname");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return \true;
		}

		if(\count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return \false;
		}

		$name = \array_shift($args);
		$reason = \implode(" ", $args);
		
		if ($sender->getServer()->getPlayer($name) instanceof Player) $target = $sender->getServer()->getPlayer($name);
		else return \false;

		$sender->getServer()->getIPBans()->addBan($target->getAddress(), $reason, \null, $sender->getName());

		if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
			$player->kick($reason !== "" ? "被ban: " . $reason : "好好反省自己！");
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.ban.success", [$player !== \null ? $player->getName() : $name]));

		return \true;
	}
}