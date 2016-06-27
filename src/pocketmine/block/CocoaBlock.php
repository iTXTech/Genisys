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

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;

class CocoaBlock extends Solid {

    protected $id = self::COCOA_BLOCK;

    public function __construct($meta = 0) {
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Cocoa Block";
    }

    public function getHardness() {
        return 0.2;
    }

    public function getResistance() {
        return 15;
    }

    public function canBeActivated() : bool {
        return true;
    }

    public function onActivate(Item $item, Player $player = null) {
        if ($item->getId() === Item::DYE and $item->getDamage() === 0x0F) {
            $block = clone $this;
            if ($block->meta > 7) {
                return false;
            }
            $block->meta += 4;
            Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));
            if (!$ev->isCancelled()) {
                $this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
            }
            $item->count--;
            return true;
        }
        return false;
    }

    public function onUpdate($type) {
        if ($type === Level::BLOCK_UPDATE_NORMAL) {
            $faces = [3, 4, 2, 5, 3, 4, 2, 5, 3, 4, 2, 5];
            if ($this->getSide($faces[$this->meta])->isTransparent() === true) {
                $this->getLevel()->useBreakOn($this);
                return Level::BLOCK_UPDATE_NORMAL;
            }
        } elseif ($type === Level::BLOCK_UPDATE_RANDOM) {
            if (mt_rand(0, 45) === 1) {
                if ($this->meta <= 7) {
                    $block = clone $this;
                    $block->meta += 4;
                    Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));
                    if (!$ev->isCancelled()) {
                        $this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
                    } else {
                        return Level::BLOCK_UPDATE_RANDOM;
                    }
                }
            } else {
                return Level::BLOCK_UPDATE_RANDOM;
            }
        }
        return false;
    }

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null) {
        if ($target->getId() === Block::WOOD and $target->getDamage() === 3) {
            if ($face !== 0 and $face !== 1) {
                $faces = [
                    2 => 0,
                    3 => 2,
                    4 => 3,
                    5 => 1,
                ];
                $this->meta = $faces[$face];
                $this->getLevel()->setBlock($block, Block::get(Item::COCOA_BLOCK, $this->meta), true);
                return true;
            }
        }
        return false;
    }

    public function getDrops(Item $item) : array {
        $drops = [];
        if ($this->meta >= 8) {
            $drops[] = [Item::DYE, 3, 3];
        } else {
            $drops[] = [Item::DYE, 3, 1];
        }
        return $drops;
    }
}
