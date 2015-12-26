<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class StatusCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.status.description",
			"%pocketmine.command.status.usage"
		);
		$this->setPermission("pocketmine.command.status");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return \true;
		}

		$mUsage = Utils::getMemoryUsage(\true);
		$rUsage = Utils::getRealMemoryUsage(\true);

		$server = $sender->getServer();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "服务器状态" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "服务器人数: ".TextFormat::GREEN . \count($sender->getServer()->getOnlinePlayers())."/".$sender->getServer()->getMaxPlayers());

		$time = \microtime(\true) - \pocketmine\START_TIME;

		$seconds = \floor($time % 60);
		$minutes = \null;
		$hours = \null;
		$days = \null;

		if($time >= 60){
			$minutes = \floor(($time % 3600) / 60);
			if($time >= 3600){
				$hours = \floor(($time % (3600 * 24)) / 3600);
				if($time >= 3600 * 24){
					$days = \floor($time / (3600 * 24));
				}
			}
		}

		$uptime = ($minutes !== \null ?
				($hours !== \null ?
					($days !== \null ?
						"$days 天 "
					: "") . "$hours 小时 "
					: "") . "$minutes 分 "
			: "") . "$seconds 秒";

		$sender->sendMessage(TextFormat::GOLD . "运行时间: " . TextFormat::RED . $uptime);

		$tpsColor = TextFormat::GREEN;
		if($server->getTicksPerSecondAverage() < 10){
			$tpsColor = TextFormat::GOLD;
		}elseif($server->getTicksPerSecondAverage() < 1){
			$tpsColor = TextFormat::RED;
		}
		
		$tpsColour = TextFormat::GREEN;
		if($server->getTicksPerSecond() < 10){
			$tpsColour = TextFormat::GOLD;
		}elseif($server->getTicksPerSecond() < 1){
			$tpsColour = TextFormat::RED;
		}

		$sender->sendMessage(TextFormat::GOLD . "平均TPS: " . $tpsColor . $server->getTicksPerSecondAverage() . " (".$server->getTickUsageAverage()."%)");
		$sender->sendMessage(TextFormat::GOLD . "瞬时TPS: " . $tpsColour . $server->getTicksPerSecond() . " (".$server->getTickUsage()."%)");

		$sender->sendMessage(TextFormat::GOLD . "网络上传: " . TextFormat::RED . \round($server->getNetwork()->getUpload() / 1024, 2) . " kB/s");
		$sender->sendMessage(TextFormat::GOLD . "网络下载: " . TextFormat::RED . \round($server->getNetwork()->getDownload() / 1024, 2) . " kB/s");

		$sender->sendMessage(TextFormat::GOLD . "线程总数: " . TextFormat::RED . Utils::getThreadCount());

		$sender->sendMessage(TextFormat::GOLD . "主线程内存: " . TextFormat::RED . \number_format(\round(($mUsage[0] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "总内存: " . TextFormat::RED . \number_format(\round(($mUsage[1] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "总虚拟内存: " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "堆栈内存: " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2)) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "系统最大内存: " . TextFormat::RED . \number_format(\round(($mUsage[2] / 1024) / 1024, 2)) . " MB.");

		if($server->getProperty("memory.global-limit") > 0){
			$sender->sendMessage(TextFormat::GOLD . "核心全局最大内存: " . TextFormat::RED . \number_format(\round($server->getProperty("memory.global-limit"), 2)) . " MB.");
		}

		foreach($server->getLevels() as $level){
			$sender->sendMessage(TextFormat::GOLD . "世界 \"".$level->getFolderName()."\"".($level->getFolderName() !== $level->getName() ? " (".$level->getName().")" : "").": " .
			TextFormat::RED . \number_format(\count($level->getChunks())) . TextFormat::GREEN . " 区块, " .
			TextFormat::RED . \number_format(\count($level->getEntities())) . TextFormat::GREEN . " 实体, " .
			TextFormat::RED . \number_format(\count($level->getTiles())) . TextFormat::GREEN . " tiles. ".
			"时间 " . (($level->getTickRate() > 1 or $level->getTickRateTime() > 40) ? TextFormat::RED : TextFormat::YELLOW) . \round($level->getTickRateTime(), 2)."毫秒" . ($level->getTickRate() > 1 ? " (tick rate ". $level->getTickRate() .")" : "")
			);
		}

		return \true;
	}
}
