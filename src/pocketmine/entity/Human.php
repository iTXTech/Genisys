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
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\inventory\FloatingInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\SimpleTransactionQueue;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Math;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\AddPlayerPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class Human extends Creature implements ProjectileSource, InventoryHolder{
	
	const DATA_PLAYER_FLAG_SLEEP = 1;
	const DATA_PLAYER_FLAG_DEAD = 2; //TODO: CHECK

	const DATA_PLAYER_FLAGS = 27;

	const DATA_PLAYER_BED_POSITION = 29;

	/** @var PlayerInventory */
	protected $inventory;

	/** @var FloatingInventory */
	protected $floatingInventory;

	/** @var SimpleTransactionQueue */
	protected $transactionQueue = null;

	/** @var UUID */
	protected $uuid;
	protected $rawUUID;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	protected $skinId;
	protected $skin;

	protected $foodTickTimer = 0;

	protected $totalXp = 0;
	protected $xpSeed;
	protected $xpCooldown = 0;

	public function getSkinData(){
		return $this->skin;
	}

	public function getSkinId(){
		return $this->skinId;
	}

	/**
	 * @return UUID|null
	 */
	public function getUniqueId(){
		return $this->uuid;
	}

	/**
	 * @return string
	 */
	public function getRawUniqueId(){
		return $this->rawUUID;
	}

	/**
	 * @param string $str
	 * @param string $skinId
	 */
	public function setSkin($str, $skinId){
		$this->skin = $str;
		$this->skinId = $skinId;
	}

	public function getFood() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getValue();
	}

	/**
	 * WARNING: This method does not check if full and may throw an exception if out of bounds.
	 * Use {@link Human::addFood()} for this purpose
	 *
	 * @param float $new
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setFood(float $new){
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$old = $attr->getValue();
		$attr->setValue($new);
		// ranges: 18-20 (regen), 7-17 (none), 1-6 (no sprint), 0 (health depletion)
		foreach([17, 6, 0] as $bound){
			if(($old > $bound) !== ($new > $bound)){
				$reset = true;
			}
		}
		if(isset($reset)){
			$this->foodTickTimer = 0;
		}

	}

	public function getMaxFood() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getMaxValue();
	}

	public function addFood(float $amount){
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$amount += $attr->getValue();
		$amount = max(min($amount, $attr->getMaxValue()), $attr->getMinValue());
		$this->setFood($amount);
	}

	public function getSaturation() : float{
		return $this->attributeMap->getAttribute(Attribute::SATURATION)->getValue();
	}

	/**
	 * WARNING: This method does not check if saturated and may throw an exception if out of bounds.
	 * Use {@link Human::addSaturation()} for this purpose
	 *
	 * @param float $saturation
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setSaturation(float $saturation){
		$this->attributeMap->getAttribute(Attribute::SATURATION)->setValue($saturation);
	}

	public function addSaturation(float $amount){
		$attr = $this->attributeMap->getAttribute(Attribute::SATURATION);
		$attr->setValue($attr->getValue() + $amount, true);
	}

	public function getExhaustion() : float{
		return $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->getValue();
	}

	/**
	 * WARNING: This method does not check if exhausted and does not consume saturation/food.
	 * Use {@link Human::exhaust()} for this purpose.
	 *
	 * @param float $exhaustion
	 */
	public function setExhaustion(float $exhaustion){
		$this->attributeMap->getAttribute(Attribute::EXHAUSTION)->setValue($exhaustion);
	}

	/**
	 * Increases a human's exhaustion level.
	 *
	 * @param float $amount
	 * @param int   $cause
	 *
	 * @return float the amount of exhaustion level increased
	 */
	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		$this->server->getPluginManager()->callEvent($ev = new PlayerExhaustEvent($this, $amount, $cause));
		if($ev->isCancelled()){
			return 0.0;
		}

		$exhaustion = $this->getExhaustion();
		$exhaustion += $ev->getAmount();

		while($exhaustion >= 4.0){
			$exhaustion -= 4.0;

			$saturation = $this->getSaturation();
			if($saturation > 0){
				$saturation = max(0, $saturation - 1.0);
				$this->setSaturation($saturation);
			}else{
				$food = $this->getFood();
				if($food > 0){
					$food--;
					$this->setFood($food);
				}
			}
		}
		$this->setExhaustion($exhaustion);

		return $ev->getAmount();
	}

	public function getXpLevel() : int{
		return (int) $this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->getValue();
	}

	public function setXpLevel(int $level) : bool{
		$this->server->getPluginManager()->callEvent($ev = new PlayerExperienceChangeEvent($this, $level, $this->getXpProgress()));
		if(!$ev->isCancelled()){
			$this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->setValue($ev->getExpLevel());
			return true;
		}
		return false;
	}

	public function addXpLevel(int $level) : bool{
		return $this->setXpLevel($this->getXpLevel() + $level);
	}

	public function takeXpLevel(int $level) : bool{
		return $this->setXpLevel($this->getXpLevel() - $level);
	}

	public function getXpProgress() : float{
		return $this->attributeMap->getAttribute(Attribute::EXPERIENCE)->getValue();
	}

	public function setXpProgress(float $progress) : bool{
		$this->attributeMap->getAttribute(Attribute::EXPERIENCE)->setValue($progress);
		return true;
	}

	public function getTotalXp() : int{
		return $this->totalXp;
	}

	/**
	 * Changes the total exp of a player
	 *
	 * @param int $xp
	 * @param bool $syncLevel This will reset the level to be in sync with the total. Usually you don't want to do this,
	 *                        because it'll mess up use of xp in anvils and enchanting tables.
	 *
	 * @return bool
	 */
	public function setTotalXp(int $xp, bool $syncLevel = false) : bool{
		$xp &= 0x7fffffff;
		if($xp === $this->totalXp){
			return false;
		}
		if(!$syncLevel){
			$level = $this->getXpLevel();
			$diff = $xp - $this->totalXp + $this->getFilledXp();
			if($diff > 0){ //adding xp
				while($diff > ($v = self::getLevelXpRequirement($level))){
					$diff -= $v;
					if(++$level >= 21863){
						$diff = $v; //fill exp bar
						break;
					}
				}
			}else{ //taking xp
				while($diff < ($v = self::getLevelXpRequirement($level - 1))){
					$diff += $v;
					if(--$level <= 0){
						$diff = 0;
						break;
					}
				}
			}
			$progress = ($diff / $v);
		}else{
			$values = self::getLevelFromXp($xp);
			$level = $values[0];
			$progress = $values[1];
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerExperienceChangeEvent($this, $level, $progress));
		if(!$ev->isCancelled()){
			$this->totalXp = $xp;
			$this->setXpLevel($ev->getExpLevel());
			$this->setXpProgress($ev->getProgress());
			return true;
		}
		return false;
	}

	public function addXp(int $xp, bool $syncLevel = false) : bool{
		return $this->setTotalXp($this->totalXp + $xp, $syncLevel);
	}

	public function takeXp(int $xp, bool $syncLevel = false) : bool{
		return $this->setTotalXp($this->totalXp - $xp, $syncLevel);
	}

	public function getRemainderXp() : int{
		return self::getLevelXpRequirement($this->getXpLevel()) - $this->getFilledXp();
	}

	public function getFilledXp() : int{
		return self::getLevelXpRequirement($this->getXpLevel()) * $this->getXpProgress();
	}

	public function recalculateXpProgress() : float{
		$this->setXpProgress($this->getRemainderXp() / self::getLevelXpRequirement($this->getXpLevel()));
	}

	public function getXpSeed() : int{
		//TODO: use this for randomizing enchantments in enchanting tables
		return $this->xpSeed;
	}

	public function resetXpCooldown(){
		$this->xpCooldown = microtime(true);
	}

	public function canPickupXp() : bool{
		return microtime(true) - $this->xpCooldown > 0.5;
	}

	/**
	 * Returns the total amount of exp required to reach the specified level.
	 *
	 * @param int $level
	 *
	 * @return int
	 */
	public static function getTotalXpRequirement(int $level) : int{
		if($level <= 16){
			return ($level ** 2) + (6 * $level);
		}elseif($level <= 31){
			return (2.5 * ($level ** 2)) - (40.5 * $level) + 360;
		}elseif($level <= 21863){
			return (4.5 * ($level ** 2)) - (162.5 * $level) + 2220;
		}
		return PHP_INT_MAX; //prevent float returns for invalid levels on 32-bit systems
	}

	/**
	 * Returns the amount of exp required to complete the specified level.
	 *
	 * @param int $level
	 *
	 * @return int
	 */
	public static function getLevelXpRequirement(int $level) : int{
		if($level <= 16){
			return (2 * $level) + 7;
		}elseif($level <= 31){
			return (5 * $level) - 38;
		}elseif($level <= 21863){
			return (9 * $level) - 158;
		}
		return PHP_INT_MAX;
	}

	/**
	 * Converts a quantity of exp into a level and a progress percentage
	 *
	 * @param int $xp
	 *
	 * @return int[]
	 */
	public static function getLevelFromXp(int $xp) : array{
		$xp &= 0x7fffffff;

		/** These values are correct up to and including level 16 */
		$a = 1;
		$b = 6;
		$c = -$xp;
		if($xp > self::getTotalXpRequirement(16)){
			/** Modify the coefficients to fit the relevant equation */
			if($xp <= self::getTotalXpRequirement(31)){
				/** Levels 16-31 */
				$a = 2.5;
				$b = -40.5;
				$c += 360;
			}else{
				/** Level 32+ */
				$a = 4.5;
				$b = -162.5;
				$c += 2220;
			}
		}

		$answer = max(Math::solveQuadratic($a, $b, $c)); //Use largest result value
		$level = floor($answer);
		$progress = $answer - $level;
		return [$level, $progress];
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getFloatingInventory(){
		return $this->floatingInventory;
	}

	public function getTransactionQueue(){
		//Is creating the transaction queue ondemand a good idea? I think only if it's destroyed afterwards. hmm...
		if($this->transactionQueue === null){
			//Potential for crashes here if a plugin attempts to use this, say for an NPC plugin or something...
			$this->transactionQueue = new SimpleTransactionQueue($this);
		}
		return $this->transactionQueue;
	}

	protected function initEntity(){
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false, self::DATA_TYPE_BYTE);
		$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0], false);

		$inventoryContents = ($this->namedtag->Inventory ?? null);
		$this->inventory = new PlayerInventory($this, $inventoryContents);

		//Virtual inventory for desktop GUI crafting and anti-cheat transaction processing
		$this->floatingInventory = new FloatingInventory($this);

		if($this instanceof Player){
			$this->addWindow($this->inventory, 0);
		}else{
			if(isset($this->namedtag->NameTag)){
				$this->setNameTag($this->namedtag["NameTag"]);
			}

			if(isset($this->namedtag->Skin) and $this->namedtag->Skin instanceof CompoundTag){
				$this->setSkin($this->namedtag->Skin["Data"], $this->namedtag->Skin["Name"]);
			}

			$this->uuid = UUID::fromData($this->getId(), $this->getSkinData(), $this->getNameTag());
		}


		parent::initEntity();

		if(!isset($this->namedtag->foodLevel) or !($this->namedtag->foodLevel instanceof IntTag)){
			$this->namedtag->foodLevel = new IntTag("foodLevel", $this->getFood());
		}else{
			$this->setFood($this->namedtag["foodLevel"]);
		}

		if(!isset($this->namedtag->foodExhaustionLevel) or !($this->namedtag->foodExhaustionLevel instanceof IntTag)){
			$this->namedtag->foodExhaustionLevel = new FloatTag("foodExhaustionLevel", $this->getExhaustion());
		}else{
			$this->setExhaustion($this->namedtag["foodExhaustionLevel"]);
		}

		if(!isset($this->namedtag->foodSaturationLevel) or !($this->namedtag->foodSaturationLevel instanceof IntTag)){
			$this->namedtag->foodSaturationLevel = new FloatTag("foodSaturationLevel", $this->getSaturation());
		}else{
			$this->setSaturation($this->namedtag["foodSaturationLevel"]);
		}

		if(!isset($this->namedtag->foodTickTimer) or !($this->namedtag->foodTickTimer instanceof IntTag)){
			$this->namedtag->foodTickTimer = new IntTag("foodTickTimer", $this->foodTickTimer);
		}else{
			$this->foodTickTimer = $this->namedtag["foodTickTimer"];
		}

		if(!isset($this->namedtag->XpLevel) or !($this->namedtag->XpLevel instanceof IntTag)){
			$this->namedtag->XpLevel = new IntTag("XpLevel", 0);
		}
		$this->setXpLevel($this->namedtag["XpLevel"]);

		if(!isset($this->namedtag->XpP) or !($this->namedtag->XpP instanceof FloatTag)){
			$this->namedtag->XpP = new FloatTag("XpP", 0);
		}
		$this->setXpProgress($this->namedtag["XpP"]);

		if(!isset($this->namedtag->XpTotal) or !($this->namedtag->XpTotal instanceof IntTag)){
			$this->namedtag->XpTotal = new IntTag("XpTotal", 0);
		}
		$this->totalXp = $this->namedtag["XpTotal"];

		if(!isset($this->namedtag->XpSeed) or !($this->namedtag->XpSeed instanceof IntTag)){
			$this->namedtag->XpSeed = new IntTag("XpSeed", mt_rand(PHP_INT_MIN, PHP_INT_MAX));
		}
		$this->xpSeed = $this->namedtag["XpSeed"];
	}

	public function getAbsorption() : int{
		return $this->attributeMap->getAttribute(Attribute::ABSORPTION)->getValue();
	}

	public function setAbsorption(int $absorption){
		$this->attributeMap->getAttribute(Attribute::ABSORPTION)->setValue($absorption);
	}

	protected function addAttributes(){
		parent::addAttributes();

		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::SATURATION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXHAUSTION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HUNGER));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HEALTH));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::MOVEMENT_SPEED));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ABSORPTION));
	}

	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->getInventory() instanceof PlayerInventory){
			$EnchantL = $this->getInventory()->getHelmet()->getEnchantmentLevel(Enchantment::TYPE_WATER_BREATHING);
		}
		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		if($this->server->foodEnabled){
			$food = $this->getFood();
			$health = $this->getHealth();
			if($food >= 18){
				$this->foodTickTimer++;
				if($this->foodTickTimer >= 80 and $health < $this->getMaxHealth()){
					$this->heal(1, new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
					$this->exhaust(3.0, PlayerExhaustEvent::CAUSE_HEALTH_REGEN);
					$this->foodTickTimer = 0;

				}
			}elseif($food === 0){
				$this->foodTickTimer++;
				if($this->foodTickTimer >= 80){
					$diff = $this->server->getDifficulty();
					$can = false;
					if($diff === 1){
						$can = $health > 10;
					}elseif($diff === 2){
						$can = $health > 1;
					}elseif($diff === 3){
						$can = true;
					}
					if($can){
						$this->attack(1, new EntityDamageEvent($this, EntityDamageEvent::CAUSE_STARVATION, 1));
					}
				}
			}
			if($food <= 6){
				if($this->isSprinting()){
					$this->setSprinting(false);
				}
			}
		}

		return $hasUpdate;
	}

	public function getName(){
		return $this->getNameTag();
	}

	public function getDrops(){
		$drops = [];
		if($this->inventory !== null){
			foreach($this->inventory->getContents() as $item){
				$drops[] = $item;
			}
		}

		return $drops;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Inventory = new ListTag("Inventory", []);
		$this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
		if($this->inventory !== null){

			//Hotbar
			for($slot = 0; $slot < $this->inventory->getHotbarSize(); ++$slot){
				$inventorySlotIndex = $this->inventory->getHotbarSlotIndex($slot);
				$item = $this->inventory->getItem($inventorySlotIndex);
				$tag = NBT::putItemHelper($item, $slot);
				$tag->TrueSlot = new ByteTag("TrueSlot", $inventorySlotIndex);
				$this->namedtag->Inventory[$slot] = $tag;
			}

			//Normal inventory
			$slotCount = $this->inventory->getSize() + $this->inventory->getHotbarSize();
			for($slot = $this->inventory->getHotbarSize(); $slot < $slotCount; ++$slot){
				$item = $this->inventory->getItem($slot - $this->inventory->getHotbarSize());
				//As NBT, real inventory slots are slots 9-44, NOT 0-35
				$this->namedtag->Inventory[$slot] = NBT::putItemHelper($item, $slot);
			}

			//Armour
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->inventory->getItem($this->inventory->getSize() + $slot - 100);
				if($item instanceof ItemItem and $item->getId() !== ItemItem::AIR){
					$this->namedtag->Inventory[$slot] = NBT::putItemHelper($item, $slot);
				}
			}
		}

		if(strlen($this->getSkinData()) > 0){
			$this->namedtag->Skin = new CompoundTag("Skin", [
				"Data" => new StringTag("Data", $this->getSkinData()),
				"Name" => new StringTag("Name", $this->getSkinId())
			]);
		}

		//Xp
		$this->namedtag->XpLevel = new IntTag("XpLevel", $this->getXpLevel());
		$this->namedtag->XpTotal = new IntTag("XpTotal", $this->getTotalXp());
		$this->namedtag->XpP = new FloatTag("XpP", $this->getXpProgress());
		$this->namedtag->XpSeed = new IntTag("XpSeed", $this->getXpSeed());

		//Food
		$this->namedtag->foodLevel = new IntTag("foodLevel", $this->getFood());
		$this->namedtag->foodExhaustionLevel = new FloatTag("foodExhaustionLevel", $this->getExhaustion());
		$this->namedtag->foodSaturationLevel = new FloatTag("foodSaturationLevel", $this->getSaturation());
		$this->namedtag->foodTickTimer = new IntTag("foodTickTimer", $this->foodTickTimer);
	}

	public function spawnTo(Player $player){
		if(strlen($this->skin) < 64 * 32 * 4){
			$e = new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
			$this->server->getLogger()->logException($e);
			$this->close();
		}elseif($player !== $this and !isset($this->hasSpawned[$player->getLoaderId()])){
			$this->hasSpawned[$player->getLoaderId()] = $player;

			if(!($this instanceof Player)){
				$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getName(), $this->skinId, $this->skin, [$player]);
			}

			$pk = new AddPlayerPacket();
			$pk->uuid = $this->getUniqueId();
			$pk->username = $this->getName();
			$pk->eid = $this->getId();
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->item = $this->getInventory()->getItemInHand();
			$pk->metadata = $this->dataProperties;
			$player->dataPacket($pk);

			$this->sendLinkedData();

			$this->inventory->sendArmorContents($player);

			if(!($this instanceof Player)){
				$this->server->removePlayerListData($this->getUniqueId(), [$player]);
			}
		}
	}

	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getLoaderId()])){

			$pk = new RemoveEntityPacket();
			$pk->eid = $this->getId();
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}

	public function close(){
		if(!$this->closed){
			if($this->getFloatingInventory() instanceof FloatingInventory){
				foreach($this->getFloatingInventory()->getContents() as $craftingItem){
					$this->level->dropItem($this, $craftingItem);
				}
			}else{
				$this->server->getLogger()->debug("Attempted to drop a null crafting inventory\n");
			}
			if(!($this instanceof Player) or $this->loggedIn){
				foreach($this->inventory->getViewers() as $viewer){
					$viewer->removeWindow($this->inventory);
				}
			}
			parent::close();
		}
	}
}
