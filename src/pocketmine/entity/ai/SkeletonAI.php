<?php

namespace pocketmine\entity\ai;

use pocketmine\entity\ai\AIHolder;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;
use pocketmine\entity\Skeleton;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\item\Item;

class SkeletonAI{

	private $AIHolder;
	
	public $width = 0.4;  //僵尸宽度
	private $dif = 0;

	public $hatred_r = 16;  //仇恨半径
	public $zo_hate_v = 1.4; //僵尸仇恨模式下的行走速度

	public function __construct(AIHolder $AIHolder){
		$this->AIHolder = $AIHolder;
		if($this->AIHolder->getServer()->aiConfig["skeleton"]){
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"SkeletonRandomWalkCalc"
			] ), 10);

			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"SkeletonRandomWalk"
			] ), 1);

			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
			"SkeletonHateWalk"
			] ), 10);
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"SkeletonHateFinder"
			] ), 10);
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"SkeletonFire"
			] ), 40);
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"array_clear"
			] ), 20 * 5);
		}
	}

	public function array_clear() {
		if (count($this->AIHolder->Skeleton) != 0) {
			foreach ($this->AIHolder->Skeleton as $eid=> $info) {
				foreach ($this->AIHolder->getServer()->getLevels() as $level) {
					if (!($level->getEntity($eid) instanceof Entity)) {
						unset($this->AIHolder->Skeleton[$eid]);
						//echo "清除 $eid \n";
					}
				}
			}
		}
	}

	public function SkeletonRandomWalkCalc() {
		$this->dif = $this->AIHolder->getServer()->getDifficulty();
		//$this->getLogger()->info("僵尸数量：".count($this->plugin->Skeleton));
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo){
				if($zo instanceof Skeleton){
					if ($this->AIHolder->willMove($zo)) {
						if (!isset($this->AIHolder->Skeleton[$zo->getId()])){
							$this->AIHolder->Skeleton[$zo->getId()] = array(
								'ID' => $zo->getId(),
								'IsChasing' => false,
								'motionx' => 0,
								'motiony' => 0,
								'motionz' => 0,
								'hurt' => 10,
								'time'=>10,
								'x' => 0,
								'y' => 0,
								'z' => 0,
								'oldv3' => $zo->getLocation(),
								'yup' => 20,
								'up' => 0,
								'yaw' => $zo->yaw,
								'pitch' => 0,
								'level' => $zo->getLevel()->getName(),
								'xxx' => 0,
								'zzz' => 0,
								'gotimer' => 10,
								'swim' => 0,
								'jump' => 0.01,
								'canjump' => true,
								'drop' => false,
								'canAttack' => 0,
								'shoot' => 20,
								'knockBack' => false,
							);
							$zom = &$this->AIHolder->Skeleton[$zo->getId()];
							$zom['x'] = $zo->getX();
							$zom['y'] = $zo->getY();
							$zom['z'] = $zo->getZ();
						}
						$zom = &$this->AIHolder->Skeleton[$zo->getId()];

						if ($zom['IsChasing'] === false) {  //自由行走模式
							if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
								//限制转动幅度
								$newmx = mt_rand(-5,5)/10;
								while (abs($newmx - $zom['motionx']) >= 0.7) {
									$newmx = mt_rand(-5,5)/10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5,5)/10;
								while (abs($newmz - $zom['motionz']) >= 0.7) {
									$newmz = mt_rand(-5,5)/10;
								}
								$zom['motionz'] = $newmz;
							}
							elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//僵尸停止
							}

							$zom['gotimer'] += 0.5;
							if ($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1,$zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->AIHolder->ifjump($zo->getLevel(),$pos);
							if ($zy === false) {  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'] ,$zom['z']);  //目标坐标
								if ($this->AIHolder->ifjump($zo->getLevel(),$pos2) === false) { //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y']-1,$zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}
								else {
									$zom['motionx'] = - $zom['motionx'];
									$zom['motionz'] = - $zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}
							else {
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1 ,$zom['z'] + $zom['motionz']);  //目标坐标
								if ($pos2->y - $zom['y'] < 0) {
									$zom['up'] = 1;
								}
								else {
									$zom['up'] = 0;
								}
							}

							if ($zom['motionx'] == 0 and $zom['motionz'] == 0) {  //僵尸停止
							}
							else {
								//转向计算
								$yaw = $this->AIHolder->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新僵尸坐标
							if (!$zom['knockBack']) {
								$zom['x'] = $pos2->getX();
								$zom['z'] = $pos2->getZ();
								$zom['y'] = $pos2->getY();
							}
							$zom['motiony'] = $pos2->getY() - $zo->getY();
							//echo($zo->getY()."\n");
							//var_dump($pos2);
							//var_dump($zom['motiony']);
							$zo->setPosition($pos2->add(0,1,0));
							//echo "SetPosition \n";
						}
					}
				}
			}
		}
	}

	public function SkeletonHateFinder() {
		foreach ($this->AIHolder->getServer()->getLevels () as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Skeleton) {
					if (isset($this->AIHolder->Skeleton[$zo->getId()])) {
						$zom = &$this->AIHolder->Skeleton[$zo->getId()];
						$h_r = $this->hatred_r;  //仇恨半径
						$pos = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
						$hatred = false;
						foreach ($zo->getViewers() as $p) {  //获取附近玩家
							if ($p->distance($pos) <= $h_r) {  //玩家在仇恨半径内
								if ($hatred === false) {
									$hatred = $p;
								} elseif ($hatred instanceof Player) {
									if ($p->distance($pos) <= $hatred->distance($pos)) {  //比上一个更近
										$hatred = $p;
									}
								}
							}
						}
						//echo ($zom['IsChasing']."\n");
						if ($hatred == false or $this->dif == 0) {
							$zom['IsChasing'] = false;
						} else {
							$zom['IsChasing'] = $hatred->getName();
						}
					}
				}
			}
		}
	}

	public function SkeletonHateWalk() {
		foreach ($this->AIHolder->getServer()->getLevels () as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Skeleton) {
					if (isset($this->AIHolder->Skeleton[$zo->getId()])) {
						$zom = &$this->AIHolder->Skeleton[$zo->getId()];
						//$zom['yup'] = $zom['yup'] - 1;
						if (!$zom['knockBack']) {
							$zom['oldv3'] = $zo->getLocation();
							$zom['canjump'] = true;

							//僵尸碰撞检测 by boybook
							foreach ($level->getEntities() as $zo0) {
								if ($zo0 instanceof Skeleton and !($zo0 == $zo)) {
									if ($zo->distance($zo0) <= $this->width * 2) {
										$dx = $zo->x - $zo0->x;
										$dz = $zo->z - $zo0->z;
										if ($dx == 0) {
											$dx = mt_rand(-5,5) / 5;
											if ($dx == 0) $dx = 1;
										}
										if ($dz == 0) {
											$dz = mt_rand(-5,5) / 5;
											if ($dz == 0) $dz = 1;
										}
										$zo->knockBack($zo0,0,$dx/5,$dz/5,0);
										$newpos = new Vector3($zo->x + $dx * 5, $zo->y, $zo->z + $dz * 5);
										$zom['x'] = $newpos->x;
										$zom['y'] = $newpos->y;
										$zom['z'] = $newpos->z;
										$this->AIHolder->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this->AIHolder,"knockBackover"],[$zo,$newpos]),5);
									}
								}

							}

							if ($zom['IsChasing'] !== false) {
								//echo ("是属于仇恨模式\n");
								$p = $this->AIHolder->getServer()->getPlayer($zom['IsChasing']);
								if (($p instanceof Player) === false) {
									$zom['IsChasing'] = false;  //取消仇恨模式
								} else {
									//真正的行走方向计算
									$xx = $p->getX() - $zo->getX();
									$zz = $p->getZ() - $zo->getZ();
									$yaw = $this->AIHolder->getyaw($xx,$zz);
									/*
									 * x = $xxx, z = $zzz
									 * x0 = $xx, z0 = $zz
									 * x^2 + z^2 = 0.7
									 * x/z = x0/z0 => x = bi * z
									 * =>
									 * bi^2 * z^2 + z^2 = 0.7
									 * z^2 * (bi^2 + 1) = 0.7
									 * */
									if ($zz != 0) {
										$bi = $xx/$zz;
									}else{
										$bi = 0;
									}

									//根据wiki：僵尸掉血后走路更快
									if ($zo->getHealth() == $zo->getMaxHealth()) {
										$zzz = sqrt(($this->zo_hate_v / 2.5) / ($bi * $bi + 1));
									}else{
										$zzz = sqrt(($this->zo_hate_v / 2) / ($bi * $bi + 1));
									}

									if ($zz < 0) $zzz = -$zzz;
									$xxx = $zzz * $bi;

									$zo_v2 = new Vector2($zo->getX(),$zo->getZ());
									$p_v2 = new Vector2($p->getX(),$p->getZ());
									if ($zo_v2->distance($p_v2) <= $this->zo_hate_v/2) {
										$xxx = $xx;
										$zzz = $zz;
									}

									$zom['xxx'] = $xxx;
									$zom['zzz'] = $zzz;

									//计算y轴
									//$width = $this->width;
									$pos0 = new Vector3 ($zo->getX(), $zo->getY() + 1, $zo->getZ());  //原坐标
									$pos = new Vector3 ($zo->getX() + $xxx, $zo->getY() + 1, $zo->getZ() + $zzz);  //目标坐标
									//用来写僵尸宽度的
									//$v = $this->zo_hate_v/2;
									//$pos_front = new Vector3 ($zo->getX() + ($xxx/$v*($v+$this->width)), $zo->getY() + 1, $zo->getZ() + ($zzz/$v*($v+$this->width)));  //前方坐标
									//$pos_back = new Vector3 ($zo->getX() + (-$xxx/$v*(-$v-$this->width)), $zo->getY() + 1, $zo->getZ() + (-$zzz/$v*(-$v-$this->width)));  //后方坐标
									$zy = $this->AIHolder->ifjump($zo->getLevel(), $pos, true);

									if ($zy === false or ($zy !== false and $this->AIHolder->ifjump($zo->getLevel(), $pos0, true, true) == 'fall')) {  //前方不可前进
										//真正的自由落体 by boybook
										if ($this->AIHolder->ifjump($zo->getLevel(), $pos0, false) === false) { //原坐标依然是悬空
											if ($zom['drop'] === false) {
												$zom['drop'] = 0;  //僵尸下落的速度
											}
											$pos2 = new Vector3 ($zo->getX(), $zo->getY() - ($zom['drop'] / 2 + 1.25), $zo->getZ());  //下降
										} else {
											$zom['drop'] = false;
											if ($this->AIHolder->whatBlock($level, $pos0) == "climb") {  //梯子
												$zy = $pos0->y + 0.7;
												$pos2 = new Vector3 ($zo->getX(), $zy - 1, $zo->getZ());  //目标坐标
											}
											elseif ($xxx != 0 and $zzz != 0) {  //走向最近距离
												if ($this->AIHolder->ifjump($zo->getLevel(), new Vector3($zo->getX() + $xxx, $zo->getY() + 1, $zo->getZ()), true) !== false) {
													$pos2 = new Vector3($zo->getX() + $xxx, floor($zo->getY()), $zo->getZ());  //目标坐标
												} elseif ($this->AIHolder->ifjump($zo->getLevel(), new Vector3($zo->getX(), $zo->getY() + 1, $zo->getZ() + $zzz), true) !== false) {
													$pos2 = new Vector3($zo->getX(), floor($zo->getY()), $zo->getZ() + $zzz);  //目标坐标
												} else {
													$pos2 = new Vector3 ($zo->getX() - $xxx / 5, floor($zo->getY()), $zo->getZ() - $zzz / 5);  //目标坐标
													//转向180度，向身后走
												}
											} else {
												$pos2 = new Vector3 ($zo->getX() - $xxx / 5, floor($zo->getY()), $zo->getZ() - $zzz / 5);  //目标坐标
											}
										}
									} else {
										$pos2 = new Vector3 ($zo->getX() + $xxx, $zy - 1, $zo->getZ() + $zzz);  //目标坐标
									}
									$zo->setPosition($pos2->add(0,1,0));

									$pos3 = $pos2;
									$pos3->y = $pos3->y + 2.62;
									$ppos = $p->getLocation();
									$ppos->y = $ppos->y + $p->getEyeHeight();
									$pitch = $this->AIHolder->getpitch($pos3,$ppos);

									$zom['yaw'] = $yaw;
									$zom['pitch'] = $pitch;
									if (!$zom['knockBack']) {
										$zom['x'] = $zo->getX();
										$zom['y'] = $zo->getY();
										$zom['z'] = $zo->getZ();
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function SkeletonRandomWalk() {
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Skeleton) {
					if (isset($this->AIHolder->Skeleton[$zo->getId()])) {
						$zom = &$this->AIHolder->Skeleton[$zo->getId()];
						if ($zom['canAttack'] != 0) {
							$zom['canAttack'] -= 1;
						}
						$pos = $zo->getLocation();
						//echo ($zom['IsChasing']."\n");

						//真正的自由落体 by boybook
						if ($zom['drop'] !== false) {
							$olddrop = $zom['drop'];
							$zom['drop'] += 0.5;
							$drop = $zom['drop'];
							//echo $drop."\n";
							$dropy = $zo->getY() - ($olddrop * 0.05 + 0.0125);
							$posd0 = new Vector3 (floor($zo->getX()), floor($dropy), floor($zo->getZ()));
							$posd = new Vector3 ($zo->getX(), $dropy, $zo->getZ());
							if ($this->AIHolder->whatBlock($zo->getLevel(), $posd0) == "air") {
								$zo->setPosition($posd->add(0,1,0));  //下降
							} else {
								for ($i = 1; $i <= $drop; $i++) {
									$posd0->y++;
									if ($this->AIHolder->whatBlock($zo->getLevel(), $posd0) != "block") {
										$posd->y = $posd0->y;
										$zo->setPosition($posd->add(0,1,0));  //下降完成
										$h = $zom['drop'] * $zom['drop'] / 20;
										$damage = $h - 3;
										//echo($h . ": " . $damage . "\n");
										if ($damage > 0) {
											//$zo->attack($damage, EntityDamageEvent::CAUSE_FALL);
											$zo->setHealth($zo->getHealth() - $damage);
										}
										$zom['drop'] = false;
										break;
									}
								}
							}
						} else {
							$drop = 0;
						}

						if ($zom['IsChasing'] !== false) {
							if (!$zom['knockBack']) {
								//echo $zy;
								$zom['up'] = 0;
								if ($this->AIHolder->whatBlock($level, $pos) == "water") {
									$zom['swim'] += 1;
									if ($zom['swim'] >= 20) $zom['swim'] = 0;
								} else {
									$zom['swim'] = 0;
								}
								//echo("目标:".$zo->getY()." ");
								//echo("原先:".$zom['oldv3']->y."\n");

								if(abs($zo->getY() - $zom['oldv3']->y) == 1 and $zom['canjump'] === true){
									//var_dump("跳");
									$zom['canjump'] = false;
									$zom['jump'] = 0.5;
								}
								else {
									if ($zom['jump'] > 0.01) {
										$zom['jump'] -= 0.1;
									}
									else {
										$zom['jump'] = 0.01;
									}
								}



								$pk3 = new SetEntityMotionPacket;
								$pk3->entities = [
									[$zo->getID(), $zom['xxx'] / 10, -$zom['swim'] / 100 + $zom['jump'] - $drop, $zom['zzz'] / 10]
								];
								foreach ($zo->getViewers() as $pl) {
									$pl->dataPacket($pk3);
								}

								$p = $this->AIHolder->getServer()->getPlayer($zom['IsChasing']);
								if ($p instanceof Player) {
									$zom['shoot'] = $zom['shoot'] -1;
									if ($zom['shoot'] <= 0) {
										//boom
									   $zom['shoot'] = 20;
									  $v3 = new Vector3($zo->getX(),$zo->getY()+2,$zo->getZ());
									$chunk = $level->getChunk($v3->x >> 4, $v3->z >> 4, true);
									$posnn = new Vector3($zo->getX(),$p->getY(),$zo->getZ());
									$my =$p->getY() - $zo->getY();
									$d = $p->distance($posnn);
									$pitch = $this->getmypitch($my, $d);
						
									$nbt2 = new CompoundTag("", [
										"Pos" => new ListTag("Pos", [
											new DoubleTag("", $zo->getX()),
											new DoubleTag("", $zo->getY()),
											new DoubleTag("", $zo->getZ())
										]),
										"Motion" => new ListTag("Motion", [
											new DoubleTag("", -\sin($zom['yaw']) * \cos($pitch / 180 * M_PI)),
											new DoubleTag("", -\sin($pitch / 180 * M_PI)),
											new DoubleTag("", \cos($zom['yaw'] / 180 * M_PI) * \cos($pitch / 180 * M_PI))
										]),
										"Rotation" => new ListTag("Rotation", [
											new FloatTag("", $zom['yaw']),
											new FloatTag("", $pitch)
										]),
									]);
									$f = 1.5;  
									   
									   
									   
									   
									   
								 //$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->chunk, $nbt, $this), $f);
									//ToDo: Make Strays shoot arrows of slowness
									$ev = new EntityShootBowEvent($zo, new ITEM(262,0), Entity::createEntity("Arrow", $chunk, $nbt2, $zo), $f);
									   
									}
									
								}
							}

						} else {
							if ($zom['up'] == 1) {
								if ($zom['yup'] <= 10) {
									$pk3 = new SetEntityMotionPacket;
									$pk3->entities = [
										[$zo->getID(), $zom['motionx'] / 10, $zom['motiony'] / 10, $zom['motionz'] / 10]
									];
									foreach ($zo->getViewers() as $pl) {
										$pl->dataPacket($pk3);
									}
								} else {
									$pk3 = new SetEntityMotionPacket;
									$pk3->entities = [
										[$zo->getID(), $zom['motionx'] / 10 - $zom['motiony'] / 10, $zom['motionz'] / 10]
									];
									foreach ($zo->getViewers() as $pl) {
										$pl->dataPacket($pk3);
									}
								}
							} else {

								$pk3 = new SetEntityMotionPacket;
								$pk3->entities = [
									[$zo->getID(), $zom['motionx'] / 10, -$zom['motiony'] / 10, $zom['motionz'] / 10]
								];
								foreach ($zo->getViewers() as $pl) {
									$pl->dataPacket($pk3);

								}
							}
						}
					}
				}
			}
		}
	}

	public function getmypitch($my, $d) {  //根据距离计算转向角度
		//转向计算
		if ($d == 0) {  //斜率不存在
			if ($my < 0) {
				$yaw = -90;
			}
			else {
				$yaw = 90;
			}
		}
		else {  //存在斜率
			if ($my >= 0 and $d > 0) {  //第一象限
				$atan = atan($my/$d);
				$yaw = rad2deg($atan);
			}
			elseif ($my >= 0 and $d < 0) {  //第二象限
				$atan = atan($my/abs($d));
				$yaw = 180 - rad2deg($atan);
			}
			elseif ($my < 0 and $d < 0) {  //第三象限
				$atan = atan($my/$d);
				$yaw = -(180 - rad2deg($atan));
			}
			elseif ($my < 0 and $d > 0) {  //第四象限
				$atan = atan(abs($my)/$d);
				$yaw = -(rad2deg($atan));
			}
		}
		
		$yaw = - $yaw;
		return $yaw;
	}
	
	public function SkeletonFire() {
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo){
				if ($zo instanceof Skeleton) {
					//var_dump($p->getLevel()->getTime());
					/* Don't use time directly
					 * Instead, get remainder of current time divided by 24,000
					 * This tells us the time of day, which is what we really need
					 */
					$timeOfDay = abs($level->getTime() % 24000);
					if(0 < $timeOfDay and $timeOfDay < 13000){
						$v3 = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
						$ok = true;
						for ($y0 = $zo->getY() + 2; $y0 <= $zo->getY()+10; $y0++) {
							$v3->y = $y0;
							if ($level->getBlock($v3)->getID() != 0) {
								$ok = false;
								break;
							}
						}
						if ($this->AIHolder->whatBlock($level,new Vector3($zo->getX(), floor($zo->getY() - 1), $zo->getZ())) == "water") $ok = false;
						if ($ok) $zo->setOnFire(2);
					}
				}
			}
		}
	}

}
