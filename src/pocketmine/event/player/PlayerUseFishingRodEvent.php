<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerUseFishingRodEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	const ACTION_START_FISHING = 0;
	const ACTION_STOP_FISHING = 1;

	private $action;

	public function __construct(Player $player, int $action = PlayerUseFishingRodEvent::ACTION_START_FISHING){
		$this->player = $player;
		$this->action = $action;
	}

	public function getAction() : int{
		return $this->action;
	}
}