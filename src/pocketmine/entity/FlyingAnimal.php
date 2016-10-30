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
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

abstract class FlyingAnimal extends Creature implements Ageable{

    protected $gravity = 0;
    protected $drag = 0.02;

    /** @var Vector3 */
    public $flyDirection = null;
    public $flySpeed = 0.5;
    public $highestY = 128;

    private $switchDirectionTicker = 0;
    public $switchDirectionTicks = 300;

    public function onUpdate($currentTick){
        if($this->closed !== false){
            return false;
        }
        if ($this->willMove(100)) {
            if(++$this->switchDirectionTicker === $this->switchDirectionTicks){
                $this->switchDirectionTicker = 0;
                if(mt_rand(0, 100) < 50){
                    $this->flyDirection = null;
                }
            }

            $this->lastUpdate = $currentTick;

            $this->timings->startTiming();

            if($this->isAlive()){

                if($this->y > $this->highestY and $this->flyDirection !== null){
                    $this->flyDirection->y = -0.5;
                }

                $inAir = !$this->isInsideOfSolid() and !$this->isInsideOfWater();
                if(!$inAir){
                    $this->flyDirection = null;
                }
                if($this->flyDirection instanceof Vector3){
                    //var_dump($this->flyDirection);
                    $this->setMotion($this->flyDirection->multiply($this->flySpeed));
                }else{
                    $this->flyDirection = $this->generateRandomDirection();
                    $this->flySpeed = mt_rand(50, 100) / 500;
                    $this->setMotion($this->flyDirection);
                }

                //$expectedPos = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);

                //$motion = $this->flyDirection->multiply($this->flySpeed);
                $this->move($this->motionX, $this->motionY, $this->motionZ);
                $this->updateMovement();
                //$this->getLevel()->addEntityMotion($this->chunk->getX(), $this->chunk->getZ(), $this->getId(), $motion->x, $motion->y, $motion->z);

                //echo "EID = {$this->getId()}, motionX = $this->motionX, motionY = $this->motionY, motionZ = $this->motionZ\n";
                /*

                if($expectedPos->distanceSquared($this) > 0){
                    $this->flyDirection = $this->generateRandomDirection();
                    $this->flySpeed = mt_rand(50, 100) / 500;
                }

                $friction = 1 - $this->drag;

                $this->motionX *= $friction;
                $this->motionY *= 1 - $this->drag;
                $this->motionZ *= $friction;
*/
                $f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
                $this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI);
                $this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);

                if($this->onGround and $this->flyDirection instanceof Vector3){
                    $this->flyDirection->y *= -1;
                }


            }
        }
        parent::onUpdate($currentTick);
        //parent::entityBaseTick();
        $this->timings->stopTiming();

        return !$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001;
    }

    private function generateRandomDirection(){
        return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
    }

	public function initEntity(){
		parent::initEntity();
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY, false);
	}

	public function isBaby(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
	}

    public function attack($damage, EntityDamageEvent $source){
        if($source->isCancelled()){
            return;
        }
        if ($source->getCause() == EntityDamageEvent::CAUSE_FALL) {
            $source->setCancelled();
            return;
        }
        parent::attack($damage, $source);
    }

}
