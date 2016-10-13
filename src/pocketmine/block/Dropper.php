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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Dropper as TileDropper;
use pocketmine\tile\Tile;
use pocketmine\utils\RedstoneUtil;

class Dropper extends Solid implements RedstoneTarget{

	protected $id = self::DROPPER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getHardness(){
		return 3.5;
	}

	public function getName() : string{
		return "Dropper";
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getTile() : TileDropper{
		$t = $this->getLevel()->getTile($this);
		$dropper = null;
		if($t instanceof TileDropper){
			$dropper = $t;
		}else{
			$nbt = new CompoundTag("", [
				new ListTag("Items", []),
				new StringTag("id", Tile::DROPPER),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			$nbt->Items->setTagType(NBT::TAG_Compound);
			$dropper = Tile::createTile(Tile::DROPPER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
		}
		return $dropper;
	}

	public function isReceivingPower() : bool{
		return RedstoneUtil::isReceivingPower($this);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$dispenser = null;
		if($player instanceof Player){
			$pitch = $player->getPitch();
			if(abs($pitch) >= 45){
				if($pitch < 0) $f = 4;
				else $f = 5;
			}else $f = $player->getDirection();
		}else $f = 0;
		$faces = [
			3 => 3,
			0 => 4,
			2 => 5,
			1 => 2,
			4 => 0,
			5 => 1
		];
		$this->meta = $faces[$f];

		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
			new ListTag("Items", []),
			new StringTag("id", Tile::DROPPER),
			new IntTag("x", $this->x),
			new IntTag("y", $this->y),
			new IntTag("z", $this->z)
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);

		if($item->hasCustomName()){
			$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
		}

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::DROPPER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		return true;
	}

	public function onUpdate($type){
		if($type == Level::BLOCK_UPDATE_NORMAL){
			if($this->isReceivingPower()){
				$dropper = $this->getTile();
				$this->shootItem($dropper->getInventory(), $dropper->getInventory()->firstOccupied());
			}
		}
	}

	public function shootItem(Inventory $inventory, int $slot){
		$item = $inventory->getItem($slot);
		$item->setCount($item->getCount() - 1);
		$inventory->setItem($slot, $item->getCount() > 0 ? $item : Item::get(Item::AIR));
		$motion = Vector3::getSideRaw($this->meta);
		$needItem = Item::get($item->getId(), $item->getDamage());
		$block = $this->getLevel()->getBlock($this->add($motion[0], $motion[1], $motion[2]));
		switch($block->getId()){
			case Block::CHEST:
			case Block::TRAPPED_CHEST:
			case Block::DROPPER:
			case Block::DISPENSER:
			case Block::BREWING_STAND_BLOCK:
			case Block::FURNACE:
				$t = $this->getLevel()->getTile($block);
				/** @var InventoryHolder $t */
				if($t instanceof Tile){
					if($t->getInventory()->canAddItem($needItem)){
						$t->getInventory()->addItem($needItem);
						return;
					}
				}
		}

		$itemTag = NBT::putItemHelper($needItem);
		$itemTag->setName("Item");


		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $this->x + $motion[0] * 2 + 0.5),
				new DoubleTag("", $this->y + ($motion[1] > 0 ? $motion[1] : 0.5)),
				new DoubleTag("", $this->z + $motion[2] * 2 + 0.5)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", $motion[0]),
				new DoubleTag("", $motion[1]),
				new DoubleTag("", $motion[2])
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", lcg_value() * 360),
				new FloatTag("", 0)
			]),
			"Health" => new ShortTag("Health", 5),
			"Item" => $itemTag,
			"PickupDelay" => new ShortTag("PickupDelay", 10)
		]);

		$f = 0.3;
		$itemEntity = Entity::createEntity("Item", $this->getTile()->chunk, $nbt, $this);
		$itemEntity->setMotion($itemEntity->getMotion()->multiply($f));
		$itemEntity->spawnToAll();

		for($i = 1; $i < 10; $i++){
			$this->getLevel()->addParticle(new SmokeParticle($this->add($motion[0] * $i * 0.3 + 0.5, $motion[1] == 0 ? 0.5 : $motion[1] * $i * 0.3, $motion[2] * $i * 0.3 + 0.5)));
		}
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			if($player->isCreative() and $player->getServer()->limitedCreative){
				return true;
			}
			$player->addWindow($this->getTile()->getInventory());
		}

		return true;
	}

	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1]
		];
	}
}
