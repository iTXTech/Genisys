<?php

namespace pocketmine\entity\ai;

use pocketmine\entity\ai\AIHolder;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;
use pocketmine\entity\IronGolem as Zombie;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class IronGolemAI{

	private $AIHolder;
	
	public $width = 0.4;  //僵尸宽度
	private $dif = 0;

	public $hatred_r = 16;  //仇恨半径
	public $zo_hate_v = 1.4; //僵尸仇恨模式下的行走速度

	public function __construct(AIHolder $AIHolder){
		$this->AIHolder = $AIHolder;
		if($this->AIHolder->getServer()->aiConfig["irongolem"]){
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"ZombieRandomWalkCalc"
			] ), 10);

			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"ZombieRandomWalk"
			] ), 1);

			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"ZombieHateWalk"
			] ), 10);
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"ZombieHateFinder"
			] ), 10);
			/*$this->plugin->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"ZombieFire"
			] ), 40);*/
			$this->AIHolder->getServer()->getScheduler ()->scheduleRepeatingTask ( new CallbackTask ( [
				$this,
				"array_clear"
			] ), 20 * 5);
		}
	}

	public function array_clear() {
		if (count($this->AIHolder->irongolem) != 0) {
			foreach ($this->AIHolder->irongolem as $eid=> $info) {
				foreach ($this->AIHolder->getServer()->getLevels() as $level) {
					if (!($level->getEntity($eid) instanceof Entity)) {
						unset($this->AIHolder->irongolem[$eid]);
						//echo "清除 $eid \n";
					}
				}
			}
		}
	}

	/**
	 * 僵尸初始化，常规化及自由行走模式循环计时器
	 * 循环间隔：20 ticks
	 */
	public function ZombieRandomWalkCalc() {
		$this->dif = $this->AIHolder->getServer()->getDifficulty();
		//$this->getLogger()->info("僵尸数量：".count($this->plugin->irongolem));
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo){
				if($zo instanceof Zombie){
					if ($this->AIHolder->willMove($zo)) {
						if (!isset($this->AIHolder->irongolem[$zo->getId()])){
							$this->AIHolder->irongolem[$zo->getId()] = array(
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
								'knockBack' => false,
							);
							$zom = &$this->AIHolder->irongolem[$zo->getId()];
							$zom['x'] = $zo->getX();
							$zom['y'] = $zo->getY();
							$zom['z'] = $zo->getZ();
						}
						$zom = &$this->AIHolder->irongolem[$zo->getId()];

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
							$zo->setPosition($pos2);
							//echo "SetPosition \n";
						}
					}
				}
			}
		}
	}

	/**
	 * 僵尸仇恨刷新计时器
	 * 循环间隔：10 ticks
	 */
	public function ZombieHateFinder() {
		foreach ($this->AIHolder->getServer()->getLevels () as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Zombie) {
					if (isset($this->AIHolder->irongolem[$zo->getId()])) {
						$zom = &$this->AIHolder->irongolem[$zo->getId()];
						$h_r = $this->hatred_r;  //仇恨半径
						$pos = new Vector3($zo->getX(), $zo->getY(), $zo->getZ());
						$hatred = false;
						foreach ($zo->getViewers() as $p) {  //获取附近玩家
							if ($p->distance($pos) <= $h_r) {  //玩家在仇恨半径内
								if ($hatred === false) {
									$hatred = $p;
								} elseif ($hatred instanceof Monster) {
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
							$zom['IsChasing'] = $hatred;
						}
					}
				}
			}
		}
	}

	/**
	 * 僵尸仇恨模式坐标更新计时器
	 * 循环间隔：10 ticks
	 */
	public function ZombieHateWalk() {
		foreach ($this->AIHolder->getServer()->getLevels () as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Zombie) {
					if (isset($this->AIHolder->irongolem[$zo->getId()])) {
						$zom = &$this->AIHolder->irongolem[$zo->getId()];
						//$zom['yup'] = $zom['yup'] - 1;
						if (!$zom['knockBack']) {
							$zom['oldv3'] = $zo->getLocation();
							$zom['canjump'] = true;

							//僵尸碰撞检测 by boybook
							foreach ($level->getEntities() as $zo0) {
								if ($zo0 instanceof Zombie and !($zo0 == $zo)) {
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
								$p = $zom['IsChasing'];
								if (($p instanceof Monster) === false) {
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
									$zo->setPosition($pos2);

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

	/**
	 * 高密集型发包计时器
	 * - 发送数据包
	 * - 计算自由落体相关数据
	 * 循环间隔：1 tick
	 */
	public function ZombieRandomWalk() {
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo) {
				if ($zo instanceof Zombie) {
					if (isset($this->AIHolder->irongolem[$zo->getId()])) {
						$zom = &$this->AIHolder->irongolem[$zo->getId()];
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
								$zo->setPosition($posd);  //下降
							} else {
								for ($i = 1; $i <= $drop; $i++) {
									$posd0->y++;
									if ($this->AIHolder->whatBlock($zo->getLevel(), $posd0) != "block") {
										$posd->y = $posd0->y;
										$zo->setPosition($posd);  //下降完成
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

								$p = $zom['IsChasing'];
								if ($p instanceof Monster) {
									if ($p->distance($pos) <= 1.3) {
										//僵尸的火焰点燃人类
										if ($zo->fireTicks > 0) {
											$p->setOnFire(1);
										}
									}
									if ($p->distance($pos) <= 1.5) {

										if ($zom['canAttack'] == 0) {
											$zom['canAttack'] = 20;
											//@$p->knockBack($zo, 0, $zom['xxx'] / 10, $zom['zzz'] / 10);
											if ($p->isSurvival()) {
												$damage = 21;
												//$damage = $this->plugin->getPlayerDamage($p, $zoDamage);
												//echo $zoDamage."-".$damage."\n";
												$p->attack($damage, new EntityDamageByEntityEvent($zo,$p,EntityDamageEvent::CAUSE_ENTITY_ATTACK,$damage));
											}
										}
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

	/**
	 * 僵尸着火计时器
	 * PM时间修复
	 */
	public function ZombieFire() {
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo){
				if ($zo instanceof Zombie) {
					//var_dump($p->getLevel()->getTime());
					if(0 < $level->getTime() and $level->getTime() < 13500){
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
