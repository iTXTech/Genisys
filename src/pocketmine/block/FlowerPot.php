<?php
/*
 * Copied from ImagicalMine
 * THIS IS COPIED FROM THE PLUGIN FlowerPot MADE BY @beito123!!
 * https://github.com/beito123/PocketMine-MP-Plugins/blob/master/test%2FFlowerPot%2Fsrc%2Fbeito%2FFlowerPot%2Fomake%2FSkull.php
 *
 * Genisys Project
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Compound;
use pocketmine\tile\FlowerPot as FlowerPotTile;

class FlowerPot extends Flowable{
	protected $id = Block::FLOWER_POT_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated(){
		return true;
	}

	public function getName(){
		return "Flower Pot Block";
	}

	public function getBoundingBox(){//todo fix...
		return new AxisAlignedBB(
			$this->x - 0.6875,
			$this->y - 0.375,
			$this->z - 0.6875,
			$this->x + 0.6875,
			$this->y + 0.375,
			$this->z + 0.6875
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent() === false){
			$this->getLevel()->setBlock($block, $this, true, true);
			$nbt = new Compound("", [
				new String("id", Tile::FLOWER_POT),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				new Int("item", 0),
				new Int("data", 0),
			]);
			$pot = Tile::createTile("FlowerPot", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			return true;
		}
		return false;
	}

	/*public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);
		return true;
	}*/

	public function onActivate(Item $item, Player $player = null){
		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof FlowerPotTile){
			if($tile->getFlowerPotItem() === Item::AIR){
				switch($item->getId()){
					case Item::TALL_GRASS:
						if($item->getDamage() === 1){
							break;
						}
					case Item::SAPLING:
					case Item::DEAD_BUSH:
					case Item::DANDELION:
					case Item::RED_FLOWER:
					case Item::BROWN_MUSHROOM:
					case Item::RED_MUSHROOM:
					case Item::CACTUS:
						$tile->setFlowerPotData($item->getId(), $item->getDamage());
						$this->setDamage($item->getId());
						$this->getLevel()->setBlock($this, $this, true, false);
						if($player->isSurvival()){
							$item->setCount($item->getCount() - 1);
							$player->getInventory()->setItemInHand($item->getCount() > 0 ? $item : Item::get(Item::AIR));
						}
						return true;
						break;
				}
			}
		}
		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}
		return false;
	}

	public function getDrops(Item $item){
		$items = array([Item::FLOWER_POT, 0, 1]);
		/** @var FlowerPotTile $tile */
		if(($tile = $this->getLevel()->getTile($this)) instanceof FlowerPotTile){
			if($tile->getFlowerPotItem() !== Item::AIR){
				$items[] = array($tile->getFlowerPotItem(), $tile->getFlowerPotData(), 1);
			}
		}
		return $items;
	}
}