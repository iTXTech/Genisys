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

namespace pocketmine\command\defaults;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\Water;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CaveCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Generate a cave",
			"%pocketmine.commands.cave.usage"
		);
		$this->setPermission("pocketmine.command.cave");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		
		//TODO: Get rid of this and add support for relative coordinaties
		if($sender instanceof Player and $args[0] == "getmypos"){
			$sender->sendMessage("Your position: ({$sender->getX()}, {$sender->getY()}, {$sender->getZ()}, {$sender->getLevel()->getFolderName()})");
			return true;
		}

		//0:旋转角度 1:洞穴长度 2:分叉数 3:洞穴强度
		if(count($args) != 8){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}
		$level = $sender->getServer()->getLevelByName($args[7]);
		if(!$level instanceof Level){
			$sender->sendMessage(TextFormat::RED ."Wrong LevelName");
			return false;
		}
		$pos = new Position($args[4], $args[5], $args[6], $level);
		$caves[0] = isset($args[0]) ? $args[0] : mt_rand(1, 360);
		$caves[1] = isset($args[1]) ? $args[1] : mt_rand(10, 300);
		$caves[2] = isset($args[2]) ? $args[2] : mt_rand(1, 6);
		$caves[4] = isset($args[3]) ? $args[3] : mt_rand(1, 10);
		$caves[3] = [false, true, true];
		$sender->sendMessage(new TranslationContainer("pocketmine.commands.cave.info", [$caves[0], $caves[1], $caves[2], $caves[3]]));
		$sender->sendMessage(new TranslationContainer(TextFormat::YELLOW . "%pocketmine.commands.cave.start"));
		$sender->sendMessage($pos->x . " " . $pos->y . " " . $pos->z);
		$this->caves($pos, $caves);
		$sender->sendMessage(new TranslationContainer(TextFormat::GREEN . "%pocketmine.commands.cave.success"));
		return true;
	}

	public function chu($v1, $v2){
		if($v2 == 0) return 0;
		return $v1 / $v2;
	}

	public function getDirectionVector($yaw, $pitch){
		$y = -\sin(\deg2rad($pitch));
		$xz = \cos(\deg2rad($pitch));
		$x = -$xz * \sin(\deg2rad($yaw));
		$z = $xz * \cos(\deg2rad($yaw));

		$temporalVector = new Vector3($x, $y, $z);
		return $temporalVector->normalize();
	}

	public function caves(Position $pos, $cave, $tt = false){
		$x = $pos->x;
		$y = $pos->y;
		$z = $pos->z;
		$level = $pos->getLevel();
		$ls = $cave[1];  //长度
		$cv = $cave[2];  //分叉数
		$lofs = $ls / $cave[2];
		$ls2 = $lofs;
		$yaw = $cave[0];
		if($cave[0] >= 0 || $cave[0] < 0){
		}else{
			$yaw = mt_rand(0, 100) * 72;
		}
		$pitch = -45;
		//$pi = M_PI / 180;
		$s1 = [$x, $y, $z];
		$s2 = [$x, $y, $z];
		//$i = -10 + mt_rand(0, 100) * 0.2;
		//$i = mt_rand(8, 25) / 10;
		$i = 1;
		for($u = 0; $u <= $ls; $u += $i){
			if($pitch > 12) $pitch = -45;
			$pitch += 5 + mt_rand(0, 5);
			$pos->getLevel()->getServer()->getLogger()->debug("[Caves] ".TextFormat::YELLOW . "yaw: $yaw  pitch: $pitch");
			if($tt) $pitch = mt_rand(0, 100) * 0.05;
			//$s2[0] = $s1[0] -\sin($yaw / 180 * M_PI) * \cos($pitch / 180 * M_PI) * $i;
			//$s2[1] = $s1[1] +\sin($pitch / 180 * M_PI) * $i;
			//$s2[2] = $s1[2] + \cos($yaw / 180 * M_PI) * \cos($pitch / 180 * M_PI) * $i;

			#echo "s1: ";
			//var_dump($s1);
			$see = $this->getDirectionVector($yaw, $pitch);
			$s2[0] = $s1[0] + $see->x * $i;
			$s2[1] = $s1[1] - $see->y * $i;
			$s2[2] = $s1[2] + $see->z * $i;
			//echo "s2: ";
			//var_dump($s2);
			if($s2[1] < 10){
				$s2[1] = 10 + mt_rand(0, 10);
			}
			if($u > $lofs){
				$cv--;
				if($cave[3][1] === false) $cv = 0;
				$lofs += $ls2;
				$newPos = new Position($s2[0], $s2[1], $s2[2], $level);
				$this->caves($newPos, [$yaw + 90 * (round(mt_rand(0, 100) / 100) * 2 - 1), $ls - $u, $cv, [false, $cave[3][1], $cave[3][2]], 0], $tt);
			}

			//$exPos = new Position($s2[0], $s2[1], $s2[2], $level);
			//$this->explodeBlocks($exPos, mt_rand(2, 4), mt_rand(1, 4));

			if(mt_rand(0, 100) > 80){
				$add = mt_rand(-10, 10);
			}else{
				$add = mt_rand(-45, 45);
			}
			$yaw = $yaw + $add;
			$yaw = $yaw % 360;
			$yaw = $yaw >= 0 ? $yaw : 360 + $yaw;

			//$i = 5 + mt_rand(0, 100) * 0.05;
			$x = $s1[0];
			$y = $s1[1];
			$z = $s1[2];
			$x2 = $s2[0];
			$y2 = $s2[1];
			$z2 = $s2[2];
			$l = max(abs($x - $x2), abs($y - $y2), abs($z - $z2));
			for($m = 0; $m <= $l; $m++){
				//$v = $level->getBlock(new Vector3(round($this->chu($x + $m, $l * ($x2 - $x))), round($y + $this->chu($m, $l * ($y2 - $y))), round($z + $this->chu($m, $l * ($z2 - $z)))))->getId();
				//if ($v != 0 and $v != 95)
				$liu = mt_rand(0, 200) == 100;
				$this->fdx(round($x + $this->chu($m, $l * ($x2 - $x))), round($y + $this->chu($m, $l * ($y2 - $y))), round($z + $this->chu($m, $l * ($z2 - $z))), $level, $liu);
			}
			$s1 = [$s2[0], $s2[1], $s2[2]];
		}
		if(mt_rand(0, 10) >= 5 and $s2[1] <= 40){
			$this->lavaSpawn($level, $s2[0], $s2[1], $s2[2]);
		}
		/*
		if ($cave[3][0]) {
			$l = $cave[4];
			$x = $s2[0];
			$y = $s2[1];
			$z = $s2[2];
			for ($i = -$l; $i <= $l; $i += 2) {
				for ($j = -$l; $j <= $l; $j += 2) {
					if ($i * $i + $j * $j <= pow($l - 0.3 * $l * mt_rand(0, 1000) / 1000, 2)) {
						if ($level->getBlock(new Vector3($x + $i, $y - 1, $z + $j))->getId() != 0) {
							$this->fdx($x + $i, $y - 1 + 2 * mt_rand(0, 1000) / 1000, $z + $j, $level);
						}
					}
					if ($i * $i + $j * $j <= pow($l - 0.5 * $l * mt_rand(0, 1000) / 1000, 2)) {
						if ($level->getBlock(new Vector3($x + $i, $y + 3, $z + $j))->getId() != 0) {
							$this->fdx($x + $i, $y + 3 + 2 * mt_rand(0, 1000) / 1000, $z + $j, $level);
						}
					}
				}
			}

			//if ($level->getBlock(new Vector3($s2[0], $s2[1] - 4, $s2[2]))->getId() != 0 && $cave[3][2] && mt_rand(0, 100) / 100 > 0.5) $this->tiankengy($level, $s2[0], $s2[1], $s2[2], $l * 0.6, 11, 0);
		} else if ($cave[3][2]) {
			$l = $cave[4];
			if ($pitch < -10 && $pitch > -45 && $level->getBlock(new Vector3($s2[0], $s2[1] - 3, $s2[2]))->getId() != 0) $this->tiankengy($level, $s2[0], $s2[1], $s2[2], $l / 2, 11, 0);
		}*/
		//echo "\n 矿洞生成完成\n";

	}

	public function lavaSpawn(Level $level, $x, $y, $z){
		$level->getServer()->getLogger()->info("生成岩浆中 " . "floor($x)" . ", " . "floor($y)" . ", " . floor($z));
		for($xx = $x - 20; $xx <= $x + 20; $xx++){
			for($zz = $z - 20; $zz <= $z + 20; $zz++){
				for($yy = $y; $yy > $y - 4; $yy--){
					$id = $level->getBlockIdAt($xx, $yy, $zz);
					if($id == 0){
						$level->setBlockIdAt($xx, $yy, $zz, 10);
						$level->setBlockDataAt($xx, $yy, $zz, 0);
					}
				}
			}
		}
		$level->setBlock(new Vector3($x, $y, $z), new Lava());
	}

	public function explodeBlocks(Position $source, $rays = 16, $size = 4){
		$vector = new Vector3(0, 0, 0);
		$vBlock = new Vector3(0, 0, 0);
		$stepLen = 0.3;
		$mRays = \intval($rays - 1);
		$affectedBlocks = array();
		for($i = 0; $i < $rays; ++$i){
			for($j = 0; $j < $rays; ++$j){
				for($k = 0; $k < $rays; ++$k){
					if($i === 0 or $i === $mRays or $j === 0 or $j === $mRays or $k === 0 or $k === $mRays){
						$vector->setComponents($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1);
						$vector->setComponents(($vector->x / ($len = $vector->length())) * $stepLen, ($vector->y / $len) * $stepLen, ($vector->z / $len) * $stepLen);
						$pointerX = $source->x;
						$pointerY = $source->y;
						$pointerZ = $source->z;

						for($blastForce = $size * (\mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $stepLen * 0.75){
							$x = (int) $pointerX;
							$y = (int) $pointerY;
							$z = (int) $pointerZ;
							$vBlock->x = $pointerX >= $x ? $x : $x - 1;
							$vBlock->y = $pointerY >= $y ? $y : $y - 1;
							$vBlock->z = $pointerZ >= $z ? $z : $z - 1;
							if($vBlock->y < 0 or $vBlock->y > 127){
								break;
							}
							$block = $source->getLevel()->getBlock($vBlock);

							if($block->getId() !== 0){
								$blastForce -= (mt_rand(1, 3) / 5 + 0.3) * $stepLen;
								if($blastForce > 0){
									if(!isset($affectedBlocks[$index = (\PHP_INT_SIZE === 8 ? ((($block->x) & 0xFFFFFFF) << 35) | ((($block->y) & 0x7f) << 28) | (($block->z) & 0xFFFFFFF) : ($block->x) . ":" . ($block->y) . ":" . ($block->z))])){
										$affectedBlocks[$index] = $block;
									}
								}
							}
							$pointerX += $vector->x;
							$pointerY += $vector->y;
							$pointerZ += $vector->z;
						}
					}
				}
			}
		}
		foreach($affectedBlocks as $block){
			if($block instanceof Block){
				$block->getLevel()->setBlock($block, new Air(), false, false);
			}
		}
	}

	public function fdx($x, $y, $z, Level $level, $liu = false){
		//$this->getLogger()->info(TextFormat::GREEN."fdx!");
		for($i = 1; $i < mt_rand(2, 4); $i++){
			$level->setBlockIdAt($x + $i - 2, $y - 1, $z + 1, 0);
			$level->setBlockIdAt($x + $i - 2, $y - 1, $z, 0);
			$level->setBlockIdAt($x + $i - 2, $y - 1, $z - 1, 0);
			$level->setBlockIdAt($x + $i - 2, $y - 1, $z - 1, 0);
			$level->setBlockIdAt($x + $i - 2, $y - 1, $z + 1, 0);
			$level->setBlockIdAt($x + $i - 2, $y + 2, $z + 1, 0);
			$level->setBlockIdAt($x + $i - 2, $y + 2, $z, 0);
			$level->setBlockIdAt($x + $i - 2, $y + 2, $z - 1, 0);
		}
		for($i = 1; $i < mt_rand(3, 6); $i++){
			$level->setBlockIdAt($x + $i - 3, $y + 1, $z + 2, 0);
			$level->setBlockIdAt($x + $i - 3, $y + 1, $z + 1, 0);
			$level->setBlockIdAt($x + $i - 3, $y + 1, $z, 0);
			$level->setBlockIdAt($x + $i - 3, $y + 1, $z - 1, 0);
			$level->setBlockIdAt($x + $i - 3, $y + 1, $z - 2, 0);
			$level->setBlockIdAt($x + $i - 3, $y, $z + 2, 0);
			$level->setBlockIdAt($x + $i - 3, $y, $z + 1, 0);
			$level->setBlockIdAt($x + $i - 3, $y, $z, 0);
			$level->setBlockIdAt($x + $i - 3, $y, $z - 1, 0);
			$level->setBlockIdAt($x + $i - 3, $y, $z - 2, 0);
		}
		if($liu){
			$l = (mt_rand(0, 1) == 0) ? new Water() : new Lava();
			$i = mt_rand(3, 6);
			$level->setBlock(new Vector3($x + $i - 3, $y + 1, $z + 3), $l);
		}
	}

	public function ranz($a){
		$n = [];
		$j = 0;
		for($m = 0; $m < $a; $m++){
			$n[] = mt_rand(0, 999) / 1000 - 1;
		}

		for($m = 0; $m < $a; $m++){
			foreach($n as $q){
				$min = min($n);
				if($n[$q] == $min){
					$n[$q] = $j;
					$j++;
					break;
				}
			}
		}
		return $n;
	}

	public function tiankengy(Level $level, $x, $y, $z, $l, $id, $bd){
		if($level->getBlock(new Vector3($x, $y, $z))->getId() == 0) $level->setBlock(new Vector3($x, $y, $z), Item::get($id, $bd)->getBlock());
		if($l >= 0){
			$random = mt_rand(0, 99999) / 100000;
			$mz = $this->ranz(4);
			foreach($mz as $sss){
				switch($mz[$sss]){
					case 0:
						if($level->getBlock(new Vector3($x, $y, $z - 1))->getId() == 0) $this->tiankengy($level, $x, $y, $z - 1, $l - $random, $id, $bd);
						break;
					case 1:
						if($level->getBlock(new Vector3($x, $y, $z + 1))->getId() == 0) $this->tiankengy($level, $x, $y, $z + 1, $l - $random, $id, $bd);
						break;
					case 2:
						if($level->getBlock(new Vector3($x + 1, $y, $z))->getId() == 0) $this->tiankengy($level, $x + 1, $y, $z, $l - $random, $id, $bd);
						break;
					case 3:
						if($level->getBlock(new Vector3($x - 1, $y, $z))->getId() == 0) $this->tiankengy($level, $x - 1, $y, $z, $l - $random, $id, $bd);
						break;
				}
			}
		}
	}

}