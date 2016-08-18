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

namespace pocketmine\entity;

use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\SpellParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class ThrownExpBottle extends Projectile{
	const NETWORK_ID = self::BOTTLE_O_ENCHANTING;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.1;
	protected $drag = 0.15;

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		$this->age++;

		if($this->age > 1200 or $this->isCollided){
			$this->kill();
			$this->close();
			$this->getLevel()->addParticle(new SpellParticle($this, 46, 82, 153));
			if($this->getLevel()->getServer()->expEnabled){
				$this->getLevel()->spawnXPOrb($this->add(0, -0.2, 0), mt_rand(1, 4));
				$this->getLevel()->spawnXPOrb($this->add(-0.1, -0.2, 0), mt_rand(1, 4));
				$this->getLevel()->spawnXPOrb($this->add(0, -0.2, -0.1), mt_rand(1, 4));
			}
			$hasUpdate = true;
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}
}