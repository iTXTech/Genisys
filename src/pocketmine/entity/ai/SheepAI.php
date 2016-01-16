<?php

namespace pocketmine\entity\ai;

use pocketmine\entity\ai\AIHolder;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;
use pocketmine\entity\Sheep;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDamageEvent;

class SheepAI{

	private $plugin;

	public $width = 0.3;
	private $dif = 0;


	public function __construct(AIHolder $plugin){
		$this->plugin = $plugin;
		if($this->plugin->getServer()->aiConfig["sheep"]){
			$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([
				$this,
				"SheepRandomWalkCalc"
			]), 5);

			$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([
				$this,
				"SheepRandomWalk"
			]), 1);
			$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([
				$this,
				"array_clear"
			]), 20 * 5);
		}
	}

	public function SheepRandomWalkCalc(){
		$this->dif = $this->plugin->getServer()->getDifficulty();
		//$this->getLogger()->info("羊数量：".count($this->plugin->Sheep));
		foreach($this->plugin->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $zo){
				if($zo instanceof Sheep){
					if($this->plugin->willMove($zo)){
						if(!isset($this->plugin->Sheep[$zo->getId()])){
							$this->plugin->Sheep[$zo->getId()] = array(
								'ID' => $zo->getId(),
								'IsChasing' => false,
								'motionx' => 0,
								'motiony' => 0,
								'motionz' => 0,
								'hurt' => 10,
								'time' => 10,
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
							$zom = &$this->plugin->Sheep[$zo->getId()];
							$zom['x'] = $zo->getX();
							$zom['y'] = $zo->getY();
							$zom['z'] = $zo->getZ();
						}
						$zom = &$this->plugin->Sheep[$zo->getId()];

						if($zom['IsChasing'] === false){  //自由行走模式
							if($zom['gotimer'] == 0 or $zom['gotimer'] == 10){
								//限制转动幅度
								$newmx = mt_rand(-5, 5) / 10;
								while(abs($newmx - $zom['motionx']) >= 0.7){
									$newmx = mt_rand(-5, 5) / 10;
								}
								$zom['motionx'] = $newmx;

								$newmz = mt_rand(-5, 5) / 10;
								while(abs($newmz - $zom['motionz']) >= 0.7){
									$newmz = mt_rand(-5, 5) / 10;
								}
								$zom['motionz'] = $newmz;
							}elseif($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24){
								$zom['motionx'] = 0;
								$zom['motionz'] = 0;
								//羊停止
							}

							$zom['gotimer'] += 0.5;
							if($zom['gotimer'] >= 22) $zom['gotimer'] = 0;  //重置走路计时器

							//$zom['motionx'] = mt_rand(-10,10)/10;
							//$zom['motionz'] = mt_rand(-10,10)/10;
							$zom['yup'] = 0;
							$zom['up'] = 0;

							//boybook的y轴判断法
							//$width = $this->width;
							$pos = new Vector3 ($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1, $zom['z'] + $zom['motionz']);  //目标坐标
							$zy = $this->plugin->ifjump($zo->getLevel(), $pos);
							if($zy === false){  //前方不可前进
								$pos2 = new Vector3 ($zom['x'], $zom['y'], $zom['z']);  //目标坐标
								if($this->plugin->ifjump($zo->getLevel(), $pos2) === false){ //原坐标依然是悬空
									$pos2 = new Vector3 ($zom['x'], $zom['y'] - 1, $zom['z']);  //下降
									$zom['up'] = 1;
									$zom['yup'] = 0;
								}else{
									$zom['motionx'] = -$zom['motionx'];
									$zom['motionz'] = -$zom['motionz'];
									//转向180度，向身后走
									$zom['up'] = 0;
								}
							}else{
								$pos2 = new Vector3 ($zom['x'] + $zom['motionx'], $zy - 1, $zom['z'] + $zom['motionz']);  //目标坐标
								if($pos2->y - $zom['y'] < 0){
									$zom['up'] = 1;
								}else{
									$zom['up'] = 0;
								}
							}

							if($zom['motionx'] == 0 and $zom['motionz'] == 0){  //羊停止
							}else{
								//转向计算
								$yaw = $this->plugin->getyaw($zom['motionx'], $zom['motionz']);
								//$zo->setRotation($yaw,0);
								$zom['yaw'] = $yaw;
								$zom['pitch'] = 0;
							}

							//更新坐标
							if(!$zom['knockBack']){
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

	public function SheepRandomWalk(){
		foreach($this->plugin->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $zo){
				if($zo instanceof Sheep){
					if(isset($this->plugin->Sheep[$zo->getId()])){
						$zom = &$this->plugin->Sheep[$zo->getId()];
						if($zom['canAttack'] != 0){
							$zom['canAttack'] -= 1;
						}
						$pos = $zo->getLocation();
						//echo ($zom['IsChasing']."\n");

						//真正的自由落体 by boybook
						if($zom['drop'] !== false){
							$olddrop = $zom['drop'];
							$zom['drop'] += 0.5;
							$drop = $zom['drop'];
							//echo $drop."\n";
							$dropy = $zo->getY() - ($olddrop * 0.05 + 0.0125);
							$posd0 = new Vector3 (floor($zo->getX()), floor($dropy), floor($zo->getZ()));
							$posd = new Vector3 ($zo->getX(), $dropy, $zo->getZ());
							if($this->plugin->whatBlock($zo->getLevel(), $posd0) == "air"){
								$zo->setPosition($posd);  //下降
							}else{
								for($i = 1; $i <= $drop; $i++){
									$posd0->y++;
									if($this->plugin->whatBlock($zo->getLevel(), $posd0) != "block"){
										$posd->y = $posd0->y;
										//$zo->setPosition($posd);  //下降完成
										$h = $zom['drop'] * $zom['drop'] / 20;
										$damage = $h - 3;
										//echo($h . ": " . $damage . "\n");
										if($damage > 0){
											$zo->attack($damage, EntityDamageEvent::CAUSE_FALL);
										}
										$zom['drop'] = false;
										break;
									}
								}
							}
						}else{
							$drop = 0;
						}
						//echo ".";
						$pk3 = new SetEntityMotionPacket;
						$pk3->entities = [
							[$zo->getID(), $zom['motionx'] / 10, 0, $zom['motionz'] / 10]
						];
						foreach($zo->getViewers() as $pl){
							$pl->dataPacket($pk3);
						}

					}
				}
			}
		}
	}

	public function array_clear(){
		if(count($this->plugin->Sheep) != 0){
			foreach($this->plugin->Sheep as $eid => $info){
				foreach($this->plugin->getServer()->getLevels() as $level){
					if(!($level->getEntity($eid) instanceof Entity)){
						unset($this->plugin->Sheep[$eid]);
						//echo "清除 $eid \n";
					}
				}
			}
		}
	}


}
