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

use pocketmine\event\entity\CreeperPowerEvent;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Creeper extends Monster{
	const NETWORK_ID = self::CREEPER;

	const DATA_SWELL_DIRECTION = 16;
	const DATA_SWELL = 17;
	const DATA_SWELL_OLD = 18;
	const DATA_POWERED = 19;
	
	const ATTACK_RADIUS = 16; //Anywhere in a 16-block horizontal radius
	const ATTACK_RADIUS_IMPAIRED = 8; //For players wearing pumpkins
	const ATTACK_HEIGHT_DIFF = 4; //+/- 4 in the Y axis

	public $dropExp = [5, 5];
	
	protected $fuse = 30;
	protected $isPrimed = false;

	/** @var Player */
	protected $target = null;
	
	public function getName() : string{
		return "Creeper";
	}
	
	public function __construct(FullChunk $chunk, CompoundTag $nbt){
		if(!isset($nbt["powered"])){
			$nbt->powered = new ByteTag("powered", false);
		}
		parent::__construct($chunk, $nbt);
		$this->setDataProperty(self::DATA_POWERED, self::DATA_TYPE_BYTE, $this->isPowered());
	}

	protected function initEntity(){
		parent::initEntity();
		if(isset($this->namedtag["Fuse"])){
			$this->fuse = (int) $this->namedtag["Fuse"];
		}else{
			$this->fuse = 30;
		}
	}
	
	public function setTarget(Human $player){
		$this->target = $player;
	}
	
	public function getTarget(): Human{
		return $this->target;
	}
	
	public function hasTarget(): bool{
		return $this->target instanceof Human and $this->target->getGamemode & 0x01 === 0x00;
	}

	public function setPowered(bool $powered, Lightning $lightning = null){
		if($lightning != null){
			$powered = true;
			$cause = CreeperPowerEvent::CAUSE_LIGHTNING;
		}else $cause = $powered ? CreeperPowerEvent::CAUSE_SET_ON : CreeperPowerEvent::CAUSE_SET_OFF;

		$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new CreeperPowerEvent($this, $lightning, $cause));

		if(!$ev->isCancelled()){
			$this->namedtag->powered = new ByteTag("powered", $powered);
			$this->setDataProperty(self::DATA_POWERED, self::DATA_TYPE_BYTE, $powered);
		}
	}

	public function isPowered() : bool{
		return (bool) $this->namedtag["powered"];
	}
	
	public function isPrimed(): bool{
		return $this->isPrimed;
	}
	
	public function getIgnitionRadius(): int{
		return (int) $this->namedtag["ExplosionRadius"] ?? 3;
	}
	
	public function ignite($withFlintSteel = false){
		$this->ignited = $withFlintSteel;
	}
	
	public function onUpdate($currentTick){
		if($this->target instanceof Human and $this->target->getGamemode() & 0x01 === 0x01){
			//Target changed gamemode to creative, search for a different target
			$this->target = null;
		}
		
		if(!$this->hasTarget()){
			$distance = PHP_INT_MAX;
			$heightDiff = PHP_INT_MAX;
			foreach($this->getViewers() as $player){
				if($p->getGamemode() & 0x01 === 0x01){
					continue;
				}
				if(($pDistance = $player->distance($this)) <= self::ATTACK_RADIUS and ($pHeightDiff = abs($player->getY() - $this->getY()) <= self::ATTACK_HEIGHT_DIFF)){
					//Found a candidate target player
					if($pDistance > $distance or $pHeightDiff > $heightDiff){
						//We already found a closer player
						continue;
					}
					if($player->getInventory()->getHelmet()->getId() === ItemItem::PUMPKIN and $pDistance > self::ATTACK_RADIUS_IMPAIRED){
						//Player is wearing a pumpkin and is further away than 8 blocks
						continue;
					}
					$this->target = $player;
					$distance = $pDistance;
					$heightDiff = $pHeightDiff;
					//Keep going until we've gone through all the possible candidates
				}
			}
		}

		if($this->hasTarget()){ //Yes, this is NOT an elseif. We use the existing target or the target selected above.
			if($this->target->distance($this) < $this->getIgnitionRadius()){
				if(!$this->isPrimed()){
					$this->ignite();
				}
				$this->fuse -= $currentTick;
				if($this->fuse <= 0){
					//$this->explode(); //TODO
				}
			}
		}else{
			$this->target = null; //hasTarget() will return false if our target is a creative/spectator player, handle this accordingly
			$this->fuse = $this->namedtag["Fuse"] ?? 30; //Reset the fuse back to default
		}

		return parent::onUpdate($currentTick);
	}
}