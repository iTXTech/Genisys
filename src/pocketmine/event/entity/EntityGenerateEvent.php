<?php
/**
 * Author: PeratX
 * OpenGenisys Project
 */
namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

class EntityGenerateEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const CAUSE_AI_HOLDER = 0;
	const CAUSE_MOB_SPAWNER = 1;

	private $cause;
	private $entityType;

	public function __construct(Entity $entity, int $cause = self::CAUSE_MOB_SPAWNER){
		$this->entity = $entity;
		$this->entityType = $entity::NETWORK_ID;
		$this->cause = $cause;
	}

	/**
	 * @return \pocketmine\level\Position
	 */
	public function getPosition(){
		return $this->entity->getPosition();
	}

	/**
	 * @return int
	 */
	public function getType() : int{
		return $this->entityType;
	}

	/**
	 * @return int
	 */
	public function getCause() : int{
		return $this->cause;
	}
}