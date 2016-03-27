<?php

namespace pocketmine\entity\ai;

use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Pig;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;

class PigAI{

	private $AIHolder;

	public $width = 0.3;
	private $dif = 0;


	public function __construct(AIHolder $AIHolder){
		$this->AIHolder = $AIHolder;
		if($this->AIHolder->getServer()->aiConfig["pig"]){
			$this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([
				$this,
				"PigRandomWalkCalc"
			]), 5);

			$this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([
				$this,
				"PigRandomWalk"
			]), 1);
		}
	}

	public function PigRandomWalkCalc(){
		$this->dif = $this->AIHolder->getServer()->getDifficulty();
		//$this->getLogger()->info("猪数量：".count($this->plugin->Pig));
		foreach($this->AIHolder->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $zo){
				if($zo::NETWORK_ID == Pig::NETWORK_ID){
					if($this->AIHolder->willMove($zo)){
						if(!isset($this->AIHolder->Pig[$zo->getId()])){
							$this->AIHolder->Pig[$zo->getId()] = array(
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
							$zom = &$this->AIHolder->Pig[$zo->getId()];
							$zom['x'] = $zo->getX();
							$zom['y'] = $zo->getY();
							$zom['z'] = $zo->getZ();
						}
						$zom = &$this->AIHolder->Pig[$zo->getId()];

						//if ($zom['IsChasing'] === false) {  //自由行走模式

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
							//猪停止
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
						$zy = $this->AIHolder->ifjump($zo->getLevel(), $pos);

						if($zy === false){  //前方不可前进
							$pos2 = new Vector3 ($zom['x'], $zom['y'], $zom['z']);  //目标坐标
							if($this->AIHolder->ifjump($zo->getLevel(), $pos2) === false){ //原坐标依然是悬空
								//	$pos2 = new Vector3 ($zom['x'], $zom['y'],$zom['z']);  //下降
								//	$zom['up'] = 1;
								$zom['yup'] = 0;
							}else{
								//	print($zy-$pos->y);
								$zom['motionx'] = -$zom['motionx'];
								$zom['motionz'] = -$zom['motionz'];
								//$zom['motiony'] = 0.01;
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

						if($zom['motionx'] == 0 and $zom['motionz'] == 0){  //牛停止
						}else{
							//转向计算
							$yaw = $this->AIHolder->getyaw($zom['motionx'], $zom['motionz']);
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
					//}

				}
			}
		}
	}

	public function PigRandomWalk(){
		foreach($this->AIHolder->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $zo){
				if($zo::NETWORK_ID == Pig::NETWORK_ID){
					if(isset($this->AIHolder->Pig[$zo->getId()])){
						$zom = &$this->AIHolder->Pig[$zo->getId()];
						if($zom['canAttack'] != 0){
							$zom['canAttack'] -= 1;
						}
						$pos = $zo->getLocation();
						//echo ($zom['IsChasing']."\n");

						//真正的自由落体 by boybook
						$downly = $zo->onGround;

						/*	if ($zo->onGround != false) {
								$downly=true;

								//$zom['motionY']=-0.04;
								//zom['drop'] += 0.01;
							} else {
								$drop = 0;

							}*/
						if(abs($zo->getY() - $zom['oldv3']->y) == 1 and $zom['canjump'] === true){
							//var_dump("跳");
							$zom['canjump'] = false;
							$zom['jump'] = 0.3;
						}else{
							if($zom['jump'] > 0.01){
								$zom['jump'] -= 0.1;
							}else{
								$zom['jump'] = 0;
							}
						}

						//echo ".";
						$pk3 = new SetEntityMotionPacket;
						$pk3->entities = [
							[$zo->getID(), $zom['xxx'], $zom['jump'] - $downly ? 0.04 : 0, $zom['zzz']]
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
		if(count($this->AIHolder->Pig) != 0){
			foreach($this->AIHolder->Pig as $eid => $info){
				foreach($this->AIHolder->getServer()->getLevels() as $level){
					if(!($level->getEntity($eid) instanceof Entity)){
						unset($this->AIHolder->Pig[$eid]);
						//echo "清除 $eid \n";
					}
				}
			}
		}
	}


}
