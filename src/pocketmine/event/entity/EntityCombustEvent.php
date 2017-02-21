<?php

/**
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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

class EntityCombustEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	protected $duration;
	protected $ProtectLevel;

	/**
	 * @param Entity $combustee
	 * @param int    $duration
	 * @param int    $ProtectLevel
	 */
	public function __construct(Entity $combustee, $duration, $ProtectLevel = 0){
		$this->entity = $combustee;
		$this->duration = $duration;
		$this->ProtectLevel = $ProtectLevel;
	}

	public function getDuration(){
		if($this->ProtectLevel !== 0){
			return round($this->duration * (1 - 0.15 * $this->ProtectLevel));
		}else{
			return $this->duration;
		}
	}

	public function setDuration($duration){
		$this->duration = (int) $duration;
	}

	public function setProtectLevel($ProtectLevel){
		$this->ProtectLevel = (int) $ProtectLevel;
	}
}