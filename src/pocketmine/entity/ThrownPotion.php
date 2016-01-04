<?php

namespace pocketmine\entity;


use pocketmine\level\format\FullChunk;
use pocketmine\level\particle\SpellParticle;
use pocketmine\nbt\tag\Compound;
use pocketmine\network\Network;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\item\Potion;
use pocketmine\entity\Effect;
use pocketmine\level\particle\Particle;
use pocketmine\level\particle\GenericParticle;

class ThrownPotion extends Projectile{
	const NETWORK_ID = 86;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;

	protected $gravity = 0.1;
	protected $drag = 0.05;
	
	public $data = 0;
	
	public function initEntity(){
		parent::initEntity();
		
		if(isset($this->namedtag->Data)){
			$this->data = $this->namedtag["Data"];
		}

		$color = Potion::getColor($this->getData());
		$this->setDataProperty(FallingSand::DATA_BLOCK_INFO, self::DATA_TYPE_LONG, (($color[0] & 0xff) << 16) | (($color[1] & 0xff) << 8) | ($color[2] & 0xff));
	}

	public function __construct(FullChunk $chunk, Compound $nbt, Entity $shootingEntity = null){
		parent::__construct($chunk, $nbt, $shootingEntity);
	}
	
	public function setData($id){
		$this->data = $id;
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function kill(){
		$color = Potion::getColor($this->getData());
		$this->getLevel()->addParticle(new SpellParticle($this, $color[0], $color[1], $color[2]));
		$players = $this->getViewers();
		foreach($players as $p) {
			if($p->distance($this) <= 6){
				switch($this->getData()) {
					case Potion::NIGHT_VISION:
						$p->addEffect(Effect::getEffect(Effect::NIGHT_VISION)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::NIGHT_VISION_T:
						$p->addEffect(Effect::getEffect(Effect::NIGHT_VISION)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::INVISIBILITY:
						$p->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::INVISIBILITY_T:
						$p->addEffect(Effect::getEffect(Effect::INVISIBILITY)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::LEAPING:
						$p->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::LEAPING_T:
						$p->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::LEAPING_TWO:
						$p->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier(1)->setDuration(1.5 * 60 * 20));
						break;
					case Potion::FIRE_RESISTANCE:
						$p->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::FIRE_RESISTANCE_T:
						$p->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::SPEED:
						$p->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::SPEED_T:
						$p->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::SPEED_TWO:
						$p->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(1)->setDuration(1.5 * 60 * 20));
						break;
					case Potion::SLOWNESS:
						$p->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(0)->setDuration(1 * 60 * 20));
						break;
					case Potion::SLOWNESS_T:
						$p->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(0)->setDuration(4 * 60 * 20));
						break;
					case Potion::WATER_BREATHING:
						$p->addEffect(Effect::getEffect(Effect::WATER_BREATHING)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::WATER_BREATHING_T:
						$p->addEffect(Effect::getEffect(Effect::WATER_BREATHING)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::POISON:
						$p->addEffect(Effect::getEffect(Effect::POISON)->setAmplifier(0)->setDuration(45 * 20));
						break;
					case Potion::POISON_T:
						$p->addEffect(Effect::getEffect(Effect::POISON)->setAmplifier(0)->setDuration(2 * 60 * 20));
						break;
					case Potion::POISON_TWO:
						$p->addEffect(Effect::getEffect(Effect::POISON)->setAmplifier(0)->setDuration(22 * 20));
						break;
					case Potion::REGENERATION:
						$p->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(0)->setDuration(45 * 20));
						break;
					case Potion::REGENERATION_T:
						$p->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(0)->setDuration(2 * 60 * 20));
						break;
					case Potion::REGENERATION_TWO:
						$p->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(22 * 20));
						break;
					case Potion::STRENGTH:
						$p->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(0)->setDuration(3 * 60 * 20));
						break;
					case Potion::STRENGTH_T:
						$p->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(0)->setDuration(8 * 60 * 20));
						break;
					case Potion::STRENGTH_TWO:
						$p->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(1)->setDuration(1.5 * 60 * 20));
						break;
					case Potion::WEAKNESS:
						$p->addEffect(Effect::getEffect(Effect::WEAKNESS)->setAmplifier(0)->setDuration(1.5 * 60 * 20));
						break;
					case Potion::WEAKNESS_T:
						$p->addEffect(Effect::getEffect(Effect::WEAKNESS)->setAmplifier(0)->setDuration(4 * 60 * 20));
						break;
					case Potion::HEALING:
						$p->addEffect(Effect::getEffect(Effect::HEALING)->setAmplifier(0)->setDuration(1));
						break;
					case Potion::HEALING_TWO:
						$p->addEffect(Effect::getEffect(Effect::HEALING)->setAmplifier(1)->setDuration(1));
						break;
					case Potion::HARMING:
						$p->addEffect(Effect::getEffect(Effect::HARMING)->setAmplifier(0)->setDuration(1));
						break;
					case Potion::HARMING_TWO:
						$p->addEffect(Effect::getEffect(Effect::HARMING)->setAmplifier(1)->setDuration(1));
						break;
				}	
			}
		}
		
		parent::kill();
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->age > 1200 or $this->isCollided){
			$this->kill();
			$this->close();
			$hasUpdate = true;
		}
		
		if($this->onGround) {
			$this->kill();
			$this->close();
		}

		$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = ThrownPotion::NETWORK_ID;
		$pk->eid = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}