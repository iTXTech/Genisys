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
 * @link   http://www.pocketmine.net/
 *
 *
 */

/**
 * Events called when a player attempts to perform movement cheats such as clipping through blocks.
 */
namespace pocketmine\event\player\cheat;

use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\math\Vector3;

class PlayerIllegalMoveEvent extends PlayerCheatEvent implements Cancellable{
	public static $handlerList = null;

	private $attemptedPosition;

	public function __construct(Player $player, Vector3 $attemptedPosition){
		$this->attemptedPosition = $attemptedPosition;
		$this->player = $player;
	}

	public function getAttemptedPosition() : Vector3{
		return $this->attemptedPosition;
	}

}