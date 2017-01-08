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

use pocketmine\block\Block;
use pocketmine\block\Rail;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

class Minecart extends Vehicle{
	const NETWORK_ID = 84;

	const TYPE_NORMAL = 1;
	const TYPE_CHEST = 2;
	const TYPE_HOPPER = 3;
	const TYPE_TNT = 4;

	const STATE_INITIAL = 0;
	const STATE_ON_RAIL = 1;
	const STATE_OFF_RAIL = 2;

	public $height = 0.7;
	public $width = 0.98;

	public $drag = 0.1;
	public $gravity = 0.5;

	public $isMoving = false;
	public $moveSpeed = 0.4;

	private $state = Minecart::STATE_INITIAL;
	private $direction = -1;
	private $moveVector = [];
	private $requestedPosition = null;

	public function initEntity(){
		$this->setMaxHealth(1);
		$this->setHealth($this->getMaxHealth());
		$this->moveVector[Entity::NORTH] = new Vector3(-1, 0, 0);
		$this->moveVector[Entity::SOUTH] = new Vector3(1, 0, 0);
		$this->moveVector[Entity::EAST] = new Vector3(0, 0, -1);
		$this->moveVector[Entity::WEST] = new Vector3(0, 0, 1);
		parent::initEntity();
	}

	public function getName() : string{
		return "Minecart";
	}

	public function getType() : int{
		return self::TYPE_NORMAL;
	}

	public function onUpdate($currentTick){
		if($this->closed !== false){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 1){
			return false;
		}

		$this->lastUpdate = $currentTick;

		$this->timings->startTiming();

		$hasUpdate = false;
		//parent::onUpdate($currentTick);

		if($this->isAlive()){
			$p = $this->getLinkedEntity();
			if($p instanceof Player){
				if($this->state === Minecart::STATE_INITIAL){
					$this->checkIfOnRail();
				}elseif($this->state === Minecart::STATE_ON_RAIL){
					$hasUpdate = $this->forwardOnRail($p);
					$this->updateMovement();
				}
			}
		}
		$this->timings->stopTiming ();

		return $hasUpdate or ! $this->onGround or abs ( $this->motionX ) > 0.00001 or abs ( $this->motionY ) > 0.00001 or abs ( $this->motionZ ) > 0.00001;
	}


	/**
	 * Check if minecart is currently on a rail and if so center the cart.
	 */
	private function checkIfOnRail() {
		for ($y = -1; $y !== 2 and $this->state === Minecart::STATE_INITIAL; $y++) {
			$positionToCheck = $this->temporalVector->setComponents($this->x, $this->y + $y, $this->z);
			$block = $this->level->getBlock($positionToCheck);
			if($this->isRail($block)){
				$minecartPosition = $positionToCheck->floor()->add(0.5, 0, 0.5);
				$this->setPosition($minecartPosition);    // Move minecart to center of rail
				$this->state = Minecart::STATE_ON_RAIL;
			}
		}
		if($this->state !== Minecart::STATE_ON_RAIL){
			$this->state = Minecart::STATE_OFF_RAIL;
		}
	}

	private function isRail($rail) {
		return ($rail !== null and in_array($rail->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL]));
	}

	private function getCurrentRail() {
		$block = $this->getLevel()->getBlock($this);
		if($this->isRail($block)){
			return $block;
		}
		// Rail could be one block below descending down
		$down = $this->temporalVector->setComponents($this->x, $this->y - 1, $this->z);
		$block = $this->getLevel()->getBlock($down);
		if($this->isRail($block)){
			return $block;
		}
		return null;
	}

	/**
	 * Attempt to move forward on rail given the direction the cart is already moving, or if not moving based
	 * on the direction the player is looking.
	 * @param Player $player Player riding the minecart.
	 * @return boolean True if minecart moved, false otherwise.
	 */
	private function forwardOnRail(Player $player) {
		if($this->direction === -1){
			$candidateDirection = $player->getDirection();
		}else{
			$candidateDirection = $this->direction;
		}
		$rail = $this->getCurrentRail();
		if ($rail !== null) {
			$railType = $rail->getDamage ();
			$nextDirection = $this->getDirectionToMove($railType, $candidateDirection);
			if ($nextDirection !== -1) {
				$this->direction = $nextDirection;
				$moved = $this->checkForVertical($railType, $nextDirection);
				if(!$moved){
					return $this->moveIfRail();
				}else{
					return true;
				}
			}else{
				$this->direction = -1;  // Was not able to determine direction to move, so wait for player to look in valid direction
			}
		}else{
			// Not able to find rail
			$this->state = Minecart::STATE_INITIAL;
		}
		return false;
	}

	/**
	 * Determine the direction the minecart should move based on the candidate direction (current direction
	 * minecart is moving, or the direction the player is looking) and the type of rail that the minecart is
	 * on.
	 * @param RailType $railType Type of rail the minecart is on.
	 * @param Direction $candidateDirection Direction minecart already moving, or direction player looking.
	 * @return Direction The direction the minecart should move.
	 */
	private function getDirectionToMove($railType, $candidateDirection) {
		switch($railType){
			case Rail::STRAIGHT_NORTH_SOUTH:
			case Rail::SLOPED_ASCENDING_NORTH:
			case Rail::SLOPED_ASCENDING_SOUTH:
				switch($candidateDirection){
					case Entity::NORTH:
					case Entity::SOUTH:
						return $candidateDirection;
				}
				break;
			case Rail::STRAIGHT_EAST_WEST:
			case Rail::SLOPED_ASCENDING_EAST:
			case Rail::SLOPED_ASCENDING_WEST:
				switch($candidateDirection){
					case Entity::WEST:
					case Entity::EAST:
						return $candidateDirection;
				}
				break;
			case Rail::CURVED_SOUTH_EAST:
				switch($candidateDirection){
					case Entity::SOUTH:
					case Entity::EAST:
						return $candidateDirection;
					case Entity::NORTH:
						return $this->checkForTurn($candidateDirection, Entity::EAST);
					case Entity::WEST:
						return $this->checkForTurn($candidateDirection, Entity::SOUTH);
				}
				break;
			case Rail::CURVED_SOUTH_WEST:
				switch($candidateDirection){
					case Entity::SOUTH:
					case Entity::WEST:
						return $candidateDirection;
					case Entity::NORTH:
						return $this->checkForTurn($candidateDirection, Entity::WEST);
					case Entity::EAST:
						return $this->checkForTurn($candidateDirection, Entity::SOUTH);
				}
				break;
			case Rail::CURVED_NORTH_WEST:
				switch ($candidateDirection) {
					case Entity::NORTH:
					case Entity::WEST:
						return $candidateDirection;
					case Entity::SOUTH:
						return $this->checkForTurn($candidateDirection, Entity::WEST);
					case Entity::EAST:
						return $this->checkForTurn($candidateDirection, Entity::NORTH);

				}
				break;
			case Rail::CURVED_NORTH_EAST:
				switch ($candidateDirection) {
					case Entity::NORTH:
					case Entity::EAST:
						return $candidateDirection;
					case Entity::SOUTH:
						return $this->checkForTurn($candidateDirection, Entity::EAST);
					case Entity::WEST:
						return $this->checkForTurn($candidateDirection, Entity::NORTH);
				}
				break;
		}
		return -1;
	}

	/**
	 * Need to alter direction on curves halfway through the turn and reset the minecart to be in the middle of
	 * the rail again so as not to collide with nearby blocks.
	 * @param Direction $currentDirection Direction minecart currently moving
	 * @param Direction $newDirection Direction minecart should turn once has hit the halfway point.
	 * @return Direction Either the current direction or the new direction depending on haw far across the rail the
	 * minecart is.
	 */
	private function checkForTurn($currentDirection, $newDirection) {
		switch($currentDirection) {
			case Entity::NORTH:
				$diff = $this->x - $this->getFloorX();
				if ($diff !== 0 and $diff <= .5) {
					$dx = ($this->getFloorX() + .5) - $this->x;
					$this->move($dx, 0, 0);
					return $newDirection;
				}
				break;
			case Entity::SOUTH:
				$diff = $this->x - $this->getFloorX();
				if ($diff !== 0 and $diff >= .5) {
					$dx = ($this->getFloorX() + .5) - $this->x;
					$this->move($dx, 0, 0);
					return $newDirection;
				}
				break;
			case Entity::EAST:
				$diff = $this->z - $this->getFloorZ();
				if ($diff !== 0 and $diff <= .5) {
					$dz = ($this->getFloorZ() + .5) - $this->z;
					$this->move(0, 0, $dz);
					return $newDirection;
				}
				break;
			case Entity::WEST:
				$diff = $this->z - $this->getFloorZ();
				if ($diff !== 0 and $diff >= .5) {
					$dz = $dz = ($this->getFloorZ() + .5) - $this->z;
					$this->move(0, 0, $dz);
					return $newDirection;
				}
				break;
		}
		return $currentDirection;
	}

	private function checkForVertical($railType, $currentDirection) {
		switch ($railType) {
			case Rail::SLOPED_ASCENDING_NORTH:
				switch($currentDirection){
					case Entity::NORTH:
						// Headed north up
						$diff = $this->x - $this->getFloorX();
						if ($diff !== 0 and $diff <= .5) {
							$dx = ($this->getFloorX() - .1) - $this->x;
							$this->move($dx, 1, 0);
							return true;
						}
						break;
					case ENTITY::SOUTH:
						// Headed south down
						$diff = $this->x - $this->getFloorX();
						if ($diff !== 0 and $diff >= .5) {
							$dx = ($this->getFloorX() + 1 ) - $this->x;
							$this->move($dx, -1, 0);
							return true;
						}
						break;
				}
				break;
			case Rail::SLOPED_ASCENDING_SOUTH:
				switch($currentDirection){
					case Entity::SOUTH:
						// Headed south up
						$diff = $this->x - $this->getFloorX();
						if ($diff !== 0 and $diff >= .5) {
							$dx = ($this->getFloorX() + 1 ) - $this->x;
							$this->move($dx, 1, 0);
							return true;
						}
						break;
					case Entity::NORTH:
						// Headed north down
						$diff = $this->x - $this->getFloorX();
						if ($diff !== 0 and $diff <= .5) {
							$dx = ($this->getFloorX() - .1) - $this->x;
							$this->move($dx, -1, 0);
							return true;
						}
						break;
				}
				break;
			case Rail::SLOPED_ASCENDING_EAST:
				switch($currentDirection){
					case Entity::EAST:
						// Headed east up
						$diff = $this->z - $this->getFloorZ();
						if ($diff !== 0 and $diff <= .5) {
							$dz = ($this->getFloorZ() - .1) - $this->z;
							$this->move(0, 1, $dz);
							return true;
						}
						break;
					case Entity::WEST:
						// Headed west down
						$diff = $this->z - $this->getFloorZ();
						if ($diff !== 0 and $diff >= .5) {
							$dz = ($this->getFloorZ() + 1) - $this->z;
							$this->move(0, -1, $dz);
							return true;
						}
						break;
				}
				break;
			case Rail::SLOPED_ASCENDING_WEST:
				switch($currentDirection){
					case Entity::WEST:
						// Headed west up
						$diff = $this->z - $this->getFloorZ();
						if ($diff !== 0 and $diff >= .5) {
							$dz = ($this->getFloorZ() + 1) - $this->z;
							$this->move(0, 1, $dz);
							return true;
						}
						break;
					case Entity::EAST:
						// Headed east down
						$diff = $this->z - $this->getFloorZ();
						if ($diff !== 0 and $diff <= .5) {
							$dz = ($this->getFloorZ() - .1) - $this->z;
							$this->move(0, -1, $dz);
							return true;
						}
						break;
				}
				break;
		}
		return false;
	}

	/**
	 * Move the minecart as long as it will still be moving on to another piece of rail.
	 * @return boolean True if the minecart moved.
	 */
	private function moveIfRail(){
		$nextMoveVector = $this->moveVector[$this->direction];
		$nextMoveVector = $nextMoveVector->multiply($this->moveSpeed);
		$newVector = $this->add($nextMoveVector->x, $nextMoveVector->y, $nextMoveVector->z);
		$possibleRail = $this->getCurrentRail();
		if(in_array($possibleRail->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL])) {
			$this->moveUsingVector($newVector);
			return true;
		}
	}

	/**
	 * Invoke the normal move code, but first need to convert the desired position vector into the
	 * delta values from the current position.
	 * @param Vector3 $desiredPosition
	 */
	private function moveUsingVector(Vector3 $desiredPosition){
		$dx = $desiredPosition->x - $this->x;
		$dy = $desiredPosition->y - $this->y;
		$dz = $desiredPosition->z - $this->z;
		$this->move($dx, $dy, $dz);
	}


	/**
	 * @return Rail
	 */
	public function getNearestRail(){
		$minX = Math::floorFloat($this->boundingBox->minX);
		$minY = Math::floorFloat($this->boundingBox->minY);
		$minZ = Math::floorFloat($this->boundingBox->minZ);
		$maxX = Math::ceilFloat($this->boundingBox->maxX);
		$maxY = Math::ceilFloat($this->boundingBox->maxY);
		$maxZ = Math::ceilFloat($this->boundingBox->maxZ);

		$rails = [];

		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					$block = $this->level->getBlock($this->temporalVector->setComponents($x, $y, $z));
					if(in_array($block->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL])) $rails[] = $block;
				}
			}
		}

		$minDistance = PHP_INT_MAX;
		$nearestRail = null;
		foreach($rails as $rail){
			$dis = $this->distance($rail);
			if($dis < $minDistance){
				$nearestRail = $rail;
				$minDistance = $dis;
			}
		}
		return $nearestRail;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Minecart::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	/*public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);

		if(!$source->isCancelled()){
			$pk = new EntityEventPacket();
			$pk->eid = $this->id;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			foreach($this->getLevel()->getPlayers() as $player){
				$player->dataPacket($pk);
			}
		}
	}

	public function getSaveId(){
		$class = new \ReflectionClass(static::class);
		return $class->getShortName();
	}*/
}
