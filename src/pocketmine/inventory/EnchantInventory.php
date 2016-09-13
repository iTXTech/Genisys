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

namespace pocketmine\inventory;

use pocketmine\block\Block;
use pocketmine\item\Dye;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentLevelTable;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\CraftingDataPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\EnchantTable;

class EnchantInventory extends TemporaryInventory{
	private $bookshelfAmount = 0;

	private $levels = [];
	/** @var EnchantmentEntry[] */
	private $entries = null;

	public function __construct(Position $pos){
		parent::__construct(new FakeBlockMenu($this, $pos), InventoryType::get(InventoryType::ENCHANT_TABLE));
	}

	/**
	 * @return EnchantTable
	 */
	public function getHolder(){
		return $this->holder;
	}
	
	public function getResultSlotIndex(){
		return -1; //enchanting tables don't have result slots, they modify the item in the target slot instead
	}

	public function onOpen(Player $who){
		parent::onOpen($who);
		if($this->levels == null){
			$this->bookshelfAmount = $this->countBookshelf();

			if($this->bookshelfAmount < 0){
				$this->bookshelfAmount = 0;
			}

			if($this->bookshelfAmount > 15){
				$this->bookshelfAmount = 15;
			}

			$base = mt_rand(1, 8) + ($this->bookshelfAmount / 2) + mt_rand(0, $this->bookshelfAmount);
			$this->levels = [
				0 => max($base / 3, 1),
				1 => (($base * 2) / 3 + 1),
				2 => max($base, $this->bookshelfAmount * 2)
			];
		}
	}

	private function randomFloat($min = 0, $max = 1){
		return $min + mt_rand() / mt_getrandmax() * ($max - $min);
	}

	public function onSlotChange($index, $before, $send){
		parent::onSlotChange($index, $before, $send);

		if($index === 0){
			$item = $this->getItem(0);
			if($item->getId() === Item::AIR){
				$this->entries = null;
			}elseif($before->getId() == Item::AIR and !$item->hasEnchantments()){
				//before enchant
				if($this->entries === null){
					$enchantAbility = Enchantment::getEnchantAbility($item);
					$this->entries = [];
					for($i = 0; $i < 3; $i++){
						$result = [];

						$level = $this->levels[$i];
						$k = $level + mt_rand(0, round(round($enchantAbility / 4) * 2)) + 1;
						$bonus = ($this->randomFloat() + $this->randomFloat() - 1) * 0.15 + 1;
						$modifiedLevel = ($k * (1 + $bonus) + 0.5);

						$possible = EnchantmentLevelTable::getPossibleEnchantments($item, $modifiedLevel);
						$weights = [];
						$total = 0;

						for($j = 0; $j < count($possible); $j++){
							$id = $possible[$j]->getId();
							$weight = Enchantment::getEnchantWeight($id);
							$weights[$j] = $weight;
							$total += $weight;
						}

						$v = mt_rand(1, $total + 1);

						$sum = 0;
						for($key = 0; $key < count($weights); ++$key){
							$sum += $weights[$key];
							if($sum >= $v){
								$key++;
								break;
							}
						}
						$key--;

						if(!isset($possible[$key])) return;
						$enchantment = $possible[$key];
						$result[] = $enchantment;
						unset($possible[$key]);

						//Extra enchantment
						while(count($possible) > 0){
							$modifiedLevel = round($modifiedLevel / 2);
							$v = mt_rand(0, 51);
							if($v <= ($modifiedLevel + 1)){

								$possible = $this->removeConflictEnchantment($enchantment, $possible);

								$weights = [];
								$total = 0;

								for($j = 0; $j < count($possible); $j++){
									$id = $possible[$j]->getId();
									$weight = Enchantment::getEnchantWeight($id);
									$weights[$j] = $weight;
									$total += $weight;
								}

								$v = mt_rand(1, $total + 1);
								$sum = 0;
								for($key = 0; $key < count($weights); ++$key){
									$sum += $weights[$key];
									if($sum >= $v){
										$key++;
										break;
									}
								}
								$key--;

								$enchantment = $possible[$key];
								$result[] = $enchantment;
								unset($possible[$key]);
							}else{
								break;
							}
						}

						$this->entries[$i] = new EnchantmentEntry($result, $level, Enchantment::getRandomName());
					}

					$this->sendEnchantmentList();
				}
			}
		}
	}

	public function onClose(Player $who){
		parent::onClose($who);

		$level = $this->getHolder()->getLevel();
		for($i = 0; $i < 2; ++$i){
			if($level instanceof Level) $level->dropItem($this->getHolder()->add(0.5, 0.5, 0.5), $this->getItem($i));
			$this->clear($i);
		}

		if(count($this->getViewers()) === 0){
			$this->levels = null;
			$this->entries = null;
			$this->bookshelfAmount = 0;
		}
	}

	/**
	 * @param Enchantment[] $ent1
	 * @param Enchantment[] $ent2
	 *
	 * @return bool
	 */
	public function checkEnts(array $ent1, array $ent2){
		foreach($ent1 as $enchantment){
			$hasResult = false;
			foreach($ent2 as $enchantment1){
				if($enchantment->equals($enchantment1)){
					$hasResult = true;
					continue;
				}
			}
			if(!$hasResult){
				return false;
			}
		}
		return true;
	}

	public function onEnchant(Player $who, Item $before, Item $after){
		$result = ($before->getId() === Item::BOOK) ? new EnchantedBook() : $before;
		if(!$before->hasEnchantments() and $after->hasEnchantments() and $after->getId() == $result->getId() and
			$this->levels != null and $this->entries != null
		){
			$enchantments = $after->getEnchantments();
			for($i = 0; $i < 3; $i++){
				if($this->checkEnts($enchantments, $this->entries[$i]->getEnchantments())){
					$lapis = $this->getItem(1);
					$level = $who->getXpLevel();
					$cost = $this->entries[$i]->getCost();
					if($lapis->getId() == Item::DYE and $lapis->getDamage() == Dye::BLUE and $lapis->getCount() > $i and $level >= $cost){
						foreach($enchantments as $enchantment){
							$result->addEnchantment($enchantment);
						}
						$this->setItem(0, $result);
						$lapis->setCount($lapis->getCount() - $i - 1);
						$this->setItem(1, $lapis);
						$who->takeXpLevel($i + 1);
						break;
					}
				}
			}
		}
	}

	public function countBookshelf() : int{
		if($this->getHolder()->getLevel()->getServer()->countBookshelf){
			$count = 0;
			$pos = $this->getHolder();
			$offsets = [[2, 0], [-2, 0], [0, 2], [0, -2], [2, 1], [2, -1], [-2, 1], [-2, 1], [1, 2], [-1, 2], [1, -2], [-1, -2]];
			for($i = 0; $i < 3; $i++){
				foreach($offsets as $offset){
					if($pos->getLevel()->getBlockIdAt($pos->x + $offset[0], $pos->y + $i, $pos->z + $offset[1]) == Block::BOOKSHELF){
						$count++;
					}
					if($count >= 15){
						break 2;
					}
				}
			}
			return $count;
		}else{
			return mt_rand(0, 15);
		}
	}

	public function sendEnchantmentList(){
		$pk = new CraftingDataPacket();
		if($this->entries !== null and $this->levels !== null){
			$list = new EnchantmentList(count($this->entries));
			for($i = 0; $i < count($this->entries); $i++){
				$list->setSlot($i, $this->entries[$i]);
			}
			$pk->addEnchantList($list);
		}

		Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
	}

	/**
	 * @param Enchantment   $enchantment
	 * @param Enchantment[] $enchantments
	 * @return Enchantment[]
	 */
	public function removeConflictEnchantment(Enchantment $enchantment, array $enchantments){
		if(count($enchantments) > 0){
			foreach($enchantments as $e){
				$id = $e->getId();
				if($id == $enchantment->getId()){
					unset($enchantments[$id]);
					continue;
				}

				if($id >= 0 and $id <= 4 and $enchantment->getId() >= 0 and $enchantment->getId() <= 4){
					//Protection
					unset($enchantments[$id]);
					continue;
				}

				if($id >= 9 and $id <= 14 and $enchantment->getId() >= 9 and $enchantment->getId() <= 14){
					//Weapon
					unset($enchantments[$id]);
					continue;
				}

				if(($id === Enchantment::TYPE_MINING_SILK_TOUCH and $enchantment->getId() === Enchantment::TYPE_MINING_FORTUNE) or ($id === Enchantment::TYPE_MINING_FORTUNE and $enchantment->getId() === Enchantment::TYPE_MINING_SILK_TOUCH)){
					//Protection
					unset($enchantments[$id]);
					continue;
				}
			}
		}
		$result = [];
		if(count($enchantments) > 0){
			foreach($enchantments as $enchantment){
				$result[] = $enchantment;
			}
		}
		return $result;
	}
}
