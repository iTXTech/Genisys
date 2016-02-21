<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */
namespace pocketmine\entity;

use pocketmine\entity\Colorable;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\entity\Creature;

class Sheep extends WalkingAnimal implements Colorable{
    const NETWORK_ID = 13;

    public $width = 1.45;
    public $height = 1.12;

    public function getName(){
        return "Sheep";
    }

    public function initEntity(){
        parent::initEntity();

        $this->setMaxHealth(8);
    }

    public function targetOption(Creature $creature, float $distance) : bool{
        if($creature instanceof Player){
            return $creature->spawned && $creature->isAlive() && !$creature->closed && $creature->getInventory()->getItemInHand()->getId() == Item::SEEDS && $distance <= 49;
        }
        return false;
    }

    public function getDrops(){
        if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
            return [Item::get(Item::WOOL, mt_rand(0, 15), 1)];
        }
        return [];
    }

}
