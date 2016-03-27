<?php
/*
 * Based on the amazing MyOwnWorld written by Zzm !!!
*/

namespace pocketmine\entity\ai;

use pocketmine\entity\IronGolem;
use pocketmine\entity\Mooshroom;
use pocketmine\entity\Ocelot;
use pocketmine\entity\PigZombie;
use pocketmine\entity\SnowGolem;
use pocketmine\entity\Wolf;
use pocketmine\event\entity\EntityGenerateEvent;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\level\format\FullChunk;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;

use pocketmine\entity\Creeper;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Cow;
use pocketmine\entity\Pig;
use pocketmine\entity\Sheep;
use pocketmine\entity\Chicken;

class AIHolder{
	public $ZombieAI;
	public $CreeperAI;
	public $SkeletonAI;
	public $CowAI;
	public $PigAI;
	public $SheepAI;
	public $ChickenAI;
	public $IronGolemAI;
	public $SnowGolemAI;
	public $PigZombieAI;

	public $zombie = [];
	public $Creeper = [];
	public $Skeleton = [];
	public $Cow = [];
	public $Pig = [];
	public $Sheep = [];
	public $Chicken = [];
	public $irongolem = [];
	public $snowgolem = [];
	public $pigzombie = [];


	public $birth_r = 30;

	public $tasks = [];

	public $server;

	public function getServer(){
		return $this->server;
	}

	public function __construct(Server $server){
		$this->server = $server;

		if($this->server->aiConfig["mobgenerate"]){
			$this->tasks['ZombieGenerate'] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
				$this,
				"MobGenerate"
			]), 20 * 45);
		}


		/*$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
			$this,
			"TimeFix"
		]), 20);*/

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask ([$this, "RotationTimer"]), 2);

		$this->ZombieAI = new ZombieAI($this);
		$this->CowAI = new CowAI($this);
		//$this->PigAI = new PigAI($this);
		//$this->SheepAI = new SheepAI($this);
		//TODO: improve AIs below
		$this->ChickenAI = new ChickenAI($this);
		$this->CreeperAI = new CreeperAI($this);
		$this->SkeletonAI = new SkeletonAI($this);

		$this->IronGolemAI = new IronGolemAI($this);
		//$this->PigZombieAI = new PigZombieAI($this);
	}

	/*
	 ************ API 部分 ************************************
	 */

	/**
	 * @param $r
	 * 设置僵尸仇恨半径
	 */
	public function setZombieHatred_r($r){
		$this->ZombieAI->hatred_r = $r;
	}

	/**
	 * @param $r
	 * 设置夜晚刷僵尸范围（以每个玩家为中心）
	 */
	public function setZombieBirth_r($r){
		$this->birth_r = $r;
	}

	/**
	 * @param $v
	 * 设置僵尸仇恨模式下的走路速度
	 */
	public function setZombieHate_v($v){
		$this->ZombieAI->zo_hate_v = $v;
	}

	/**
	 * @param $tick
	 * @return bool
	 * 重新启动刷怪计时器
	 * （可用于更改刷怪时间间隔）
	 */
	public function RestartSpawnTimer($tick = 600){
		$task = $this->tasks['ZombieGenerate'];
		if($task instanceof TaskHandler){
			//TODO 没试验过是否有效。。。
			$task->cancel();
			$task->run($tick);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return bool
	 * 停止刷怪计时器
	 */
	public function CancelSpawnTimer(){
		$task = $this->tasks['ZombieGenerate'];
		if($task instanceof TaskHandler){
			$task->cancel();
			return true;
		}else{
			return false;
		}
	}

	public function TimeFix(){
		foreach($this->getServer()->getLevels() as $level){
			if($level->getTime() > 24000){
				$level->setTime(0);
			}
		}
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只僵尸在某坐标
	 */
	public function spawnZombie(Position $pos, $maxHealth = 20, $health = 20){
		$this->getZombie($pos, $maxHealth, $health)->spawnToAll();
		//$this->getLogger()->info("生成了一只僵尸");
	}

	public function getZombie(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Zombie($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		return $zo;
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只苦力怕在某坐标
	 */
	public function spawnCreeper(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Creeper($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		$zo->spawnToAll();
		//$this->getLogger()->info("生成了一只苦力怕");
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只骷髅弓箭手在某坐标
	 */
	public function spawnSkeleton(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Skeleton($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		$zo->spawnToAll();
		//$this->getLogger()->info("生成了一只骷髅弓箭手");
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只牛在某坐标
	 *
	 * @return Cow
	 */
	public function getCow(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Cow($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		return $zo;
		//$this->getLogger()->info("生成了一只牛");
	}

	public function spawnCow(Position $pos, $maxHealth = 20, $health = 20){
		$this->getCow($pos, $maxHealth, $health)->spawnToAll();
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只豬在某坐标
	 */
	public function spawnPig(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Pig($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		$zo->spawnToAll();
		//$this->getLogger()->info("生成了一只豬");
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只羊在某坐标
	 */
	public function spawnSheep(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Sheep($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		$zo->spawnToAll();
		//$this->getLogger()->info("生成了一只羊");
	}

	/**
	 * @param Position $pos 出生位置坐标(世界)
	 * @param int      $maxHealth 最高血量
	 * @param int      $health 血量
	 *                            出生一只雞在某坐标
	 */
	public function spawnChicken(Position $pos, $maxHealth = 20, $health = 20){
		$chunk = $pos->level->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$nbt = $this->getNBT();
		$zo = new Chicken($chunk, $nbt);
		$zo->setPosition($pos);
		$zo->setMaxHealth($maxHealth);
		$zo->setHealth($health);
		$zo->spawnToAll();
		//$this->getLogger()->info("生成了一只雞");
	}

	/**
	 * @param $zoHealth
	 * @return int
	 * 根据僵尸血量获取对应攻击值
	 */
	public function getZombieDamage($zoHealth){
		$dif = $this->getServer()->getDifficulty();
		switch($dif){
			case 0:
				return 0;
				break;
			case 1:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 2;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 3;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 3;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 4;
				}else return 5;
				break;
			case 2:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 3;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 4;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 5;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 6;
				}else return 7;
				break;
			case 3:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 4;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 6;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 7;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 9;
				}else return 10;
				break;
		}
		return 0;
	}

	public function getSkeletonDamage($zoHealth){
		$dif = $this->getServer()->getDifficulty();
		switch($dif){
			case 0:
				return 0;
				break;
			case 1:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 2;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 3;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 3;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 4;
				}else return 5;
				break;
			case 2:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 3;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 4;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 5;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 6;
				}else return 7;
				break;
			case 3:
				if($zoHealth <= 20 and $zoHealth >= 16){
					return 4;
				}elseif($zoHealth <= 11 and $zoHealth >= 15){
					return 6;
				}elseif($zoHealth <= 6 and $zoHealth >= 10){
					return 7;
				}elseif($zoHealth <= 1 and $zoHealth >= 5){
					return 9;
				}else return 10;
				break;
		}
		return 0;
	}

	/**
	 * @param Player $player
	 * @param        $damage
	 * @return float
	 * 根据玩家的装备获取玩家应受到的伤害值
	 */
	public function getPlayerDamage(Player $player, $damage){
		$armorValues = [
			Item::LEATHER_CAP => 1,
			Item::LEATHER_TUNIC => 3,
			Item::LEATHER_PANTS => 2,
			Item::LEATHER_BOOTS => 1,
			Item::CHAIN_HELMET => 1,
			Item::CHAIN_CHESTPLATE => 5,
			Item::CHAIN_LEGGINGS => 4,
			Item::CHAIN_BOOTS => 1,
			Item::GOLD_HELMET => 1,
			Item::GOLD_CHESTPLATE => 5,
			Item::GOLD_LEGGINGS => 3,
			Item::GOLD_BOOTS => 1,
			Item::IRON_HELMET => 2,
			Item::IRON_CHESTPLATE => 6,
			Item::IRON_LEGGINGS => 5,
			Item::IRON_BOOTS => 2,
			Item::DIAMOND_HELMET => 3,
			Item::DIAMOND_CHESTPLATE => 8,
			Item::DIAMOND_LEGGINGS => 6,
			Item::DIAMOND_BOOTS => 3,
		];
		$points = 0;
		foreach($player->getInventory()->getArmorContents() as $index => $i){
			if(isset($armorValues[$i->getId()])){
				$points += $armorValues[$i->getId()];
			}
		}
		$damage = floor($damage - $points * 0.04);
		if($damage < 0){
			$damage = 0;
		}
		return $damage;
	}

	/**
	 * @return CompoundTag
	 * 返回一个空的实体通用NBT
	 */
	public function getNBT() : CompoundTag{
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", 0),
				new FloatTag("", 0)
			]),
		]);
		return $nbt;
	}

	/**
	 * @param Position $pos
	 * @return int
	 * 获取某坐标(位置)的亮度
	 */
	public function getLight(Position $pos){
		$chunk = $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4, false);
		$l = 0;
		if($chunk instanceof FullChunk){
			$l = $chunk->getBlockSkyLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f);
			if($l < 15){
				//$l = \max($chunk->getBlockLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f));
				$l = $chunk->getBlockLight($pos->x & 0x0f, $pos->y & 0x7f, $pos->z & 0x0f);
			}
		}
		return $l;
	}

	/******** API结束 以下为计时器 *****************************/

	/**
	 * @param Entity $entity
	 * @return bool
	 * 判断某生物周边32格内是否有玩家存在
	 * 控制僵尸是否移动（自由行走模式）
	 */
	public function willMove(Entity $entity){
		foreach($entity->getViewers() as $viewer){
			if($entity->distance($viewer->getLocation()) <= 32) return true;
		}
		return false;
	}

	public function RotationTimer(){
		foreach($this->getServer()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if($entity instanceof Zombie or $entity instanceof Creeper or $entity instanceof Skeleton or $entity instanceof Cow or $entity instanceof Pig or $entity instanceof Sheep or $entity
					instanceof Chicken or $entity instanceof Mooshroom or $entity instanceof Ocelot or $entity instanceof Wolf or $entity instanceof PigZombie
				){
					if(count($entity->getViewers()) != 0){
						if($entity instanceof Zombie or $entity instanceof PigZombie){
							$array = &$this->zombie;
						}elseif($entity instanceof Creeper){
							$array = &$this->Creeper;
						}elseif($entity instanceof Skeleton){
							$array = &$this->Skeleton;
						}elseif($entity instanceof Cow or $entity instanceof Mooshroom or $entity instanceof Pig or $entity instanceof Sheep or $entity instanceof Ocelot or $entity instanceof Wolf){
							$array = &$this->Cow;
						}elseif($entity instanceof Pig){
							$array = &$this->Pig;
						}elseif($entity instanceof Sheep){
							$array = &$this->Sheep;
						}elseif($entity instanceof Chicken){
							$array = &$this->Chicken;
						}elseif($entity instanceof IronGolem){
							$array = &$this->irongolem;
						}elseif($entity instanceof SnowGolem){
							$array = &$this->snowgolem;
						}
						if(isset($array[$entity->getId()])){
							$yaw0 = $entity->yaw;  //实际yaw
							$yaw = $array[$entity->getId()]['yaw']; //目标yaw
							//$this->getLogger()->info($yaw0.' '.$yaw);
							if(abs($yaw0 - $yaw) <= 180){  //-180到+180正方向
								if($yaw0 <= $yaw){  //实际在目标左边
									if($yaw - $yaw0 <= 15){
										$yaw0 = $yaw;
									}else{
										$yaw0 += 15;
									}
								}else{  ////实际在目标右边
									if($yaw0 - $yaw <= 15){
										$yaw0 = $yaw;
									}else{
										$yaw0 -= 15;
									}
								}
							}else{  ////+180到-180方向
								if($yaw0 >= $yaw){  //实际在目标左边
									if((180 - $yaw0) + ($yaw + 180) <= 15){
										$yaw0 = $yaw;
									}else{
										$yaw0 += 15;
										if($yaw0 >= 180) $yaw0 = $yaw0 - 360;
									}
								}else{  ////实际在目标右边
									if((180 - $yaw) - ($yaw0 + 180) <= 15){
										$yaw0 = $yaw;
									}else{
										$yaw0 -= 15;
										if($yaw0 <= 180) $yaw0 = $yaw0 + 360;
									}
								}
							}
							$pitch0 = $entity->pitch;  //实际pitch
							$pitch = $array[$entity->getId()]['pitch']; //目标pitch

							if(abs($pitch0 - $pitch) <= 15){
								$pitch0 = $pitch;
							}elseif($pitch > $pitch0){
								$pitch0 += 10;
							}elseif($pitch < $pitch0){
								$pitch0 -= 10;
							}

							$entity->setRotation($yaw0, $pitch0);
							//$this->RotateHead($entity,$yaw);
						}
					}
				}
			}
		}
	}

	/**
	 * @param $mx
	 * @param $mz
	 * @return float|int
	 * 获取yaw角度
	 */
	public function getyaw($mx, $mz){  //根据motion计算转向角度
		//转向计算
		if($mz == 0){  //斜率不存在
			if($mx < 0){
				$yaw = -90;
			}else{
				$yaw = 90;
			}
		}else{  //存在斜率
			if($mx >= 0 and $mz > 0){  //第一象限
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			}elseif($mx >= 0 and $mz < 0){  //第二象限
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			}elseif($mx < 0 and $mz < 0){  //第三象限
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			}elseif($mx < 0 and $mz > 0){  //第四象限
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			}else{
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}

	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 * @return float|int
	 * 获取pitch角度
	 */
	public function getpitch(Vector3 $from, Vector3 $to){
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if($height > 0){
			return -rad2deg(asin($height / $distance));
		}elseif($height < 0){
			return rad2deg(asin(-$height / $distance));
		}else{
			return 0;
		}
	}

	/**
	 * @param Level   $level
	 * @param Vector3 $v3
	 * @param bool    $hate
	 * @param bool    $reason
	 * @return bool|float|string
	 * 判断某坐标是否可以行走
	 * 并给出原因
	 */
	public function ifjump(Level $level, Vector3 $v3, $hate = false, $reason = false){  //boybook Y轴算法核心函数
		$x = floor($v3->getX());
		$y = floor($v3->getY());
		$z = floor($v3->getZ());

		//echo ($y." ");
		if($this->whatBlock($level, new Vector3($x, $y, $z)) == "air"){
			//echo "前方空气 ";
			if($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "block" or new Vector3($x, $y - 1, $z) == "climb"){  //方块
				//echo "考虑向前 ";
				if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  //上方一格被堵住了
					//echo "上方卡住 \n";
					if($reason) return 'up!';
					return false;  //上方卡住
				}else{
					//echo "GO向前走 \n";
					if($reason) return 'GO';
					return $y;  //向前走
				}
			}/*elseif ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "water") {  //水
			//echo "下水游泳 \n";
				if ($reason) return 'swim';
				return $y-1;  //降低一格向前走（下水游泳）
			}*/
			elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "half"){  //半砖
				//echo "下到半砖 \n";
				if($reason) return 'half';
				return $y - 0.5;  //向下跳0.5格
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "lava"){  //岩浆
				//echo "前方岩浆 \n";
				if($reason) return 'lava';
				return false;  //前方岩浆
			}elseif($this->whatBlock($level, new Vector3($x, $y - 1, $z)) == "air"){  //空气
				//echo "考虑向下跳 ";
				if($this->whatBlock($level, new Vector3($x, $y - 2, $z)) == "block"){
					//echo "GO向下跳 \n";
					if($reason) return 'down';
					return $y - 1;  //向下跳
				}else{ //前方悬崖
					//echo "前方悬崖 \n";
					if($reason) return 'fall';
					/*	if ($hate === false) {
							return false;
						}
						else {
							return $y-1;  //向下跳
						}*/
				}
			}
		}/*
		elseif ($this->whatBlock($level,new Vector3($x,$y,$z)) == "water") {  //水
		//echo "正在水中";
			if ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "water") {  //上面还是水
			//echo "向上游 \n";
				if ($reason) return 'inwater';
				return $y+1;  //向上游，防溺水
			}
			elseif ($this->whatBlock($level,new Vector3($x,$y+1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y+1,$z)) == "half") {  //上方一格被堵住了
				if ($this->whatBlock($level,new Vector3($x,$y-1,$z)) == "block" or $this->whatBlock($level,new Vector3($x,$y-1,$z)) == "half") {  //下方一格被也堵住了
				//echo "上下都被卡住 \n";
					if ($reason) return 'up!_down!';
					return false;  //上下都被卡住
				}
				else {
				//echo "向下游 \n";
					if ($reason) return 'up!';
					return $y-1;  //向下游，防卡住
				}
			}
			else {
			//echo "游泳ing... \n";
				return $y;  //向前游
			}
		}*/
		elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "half"){  //半砖
			//echo "前方半砖 \n";
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 1, $z)) == "high"){  //上方一格被堵住了
				//return false;  //上方卡住
			}else{
				if($reason) return 'halfGO';
				return $y + 0.5;
			}

		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "lava"){  //岩浆
			//echo "前方岩浆 \n";
			if($reason) return 'lava';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "high"){  //1.5格高方块
			//echo "前方栅栏 \n";
			if($reason) return 'high';
			return false;
		}elseif($this->whatBlock($level, new Vector3($x, $y, $z)) == "climb"){  //梯子
			//echo "前方梯子 \n";
			//return $y;
			if($reason) return 'climb';
			if($hate){
				return $y + 0.7;
			}else{
				return $y + 0.5;
			}
		}else{  //考虑向上
			//echo "考虑向上 ";
			if($this->whatBlock($level, new Vector3($x, $y + 1, $z)) != "air"){  //前方是面墙
				//echo "前方是墙 \n";
				if($reason) return 'wall';
				return false;
			}else{
				if($this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "block" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "half" or $this->whatBlock($level, new Vector3($x, $y + 2, $z)) == "high"){  //上方两格被堵住了
					//echo "2格处被堵 \n";
					if($reason) return 'up2!';
					return false;
				}else{
					//echo "GO向上跳 \n";
					if($reason) return 'upGO';
					return $y + 1;  //向上跳
				}
			}
		}
		return false;
	}

	public function whatBlock(Level $level, $v3){  //boybook的y轴判断法 核心 什么方块？
		$block = $level->getBlock($v3);
		$id = $block->getID();
		$damage = $block->getDamage();
		switch($id){
			case 0:
			case 6:
			case 27:
			case 30:
			case 31:
			case 37:
			case 38:
			case 39:
			case 40:
			case 50:
			case 51:
			case 63:
			case 66:
			case 68:
			case 78:
			case 111:
			case 141:
			case 142:
			case 171:
			case 175:
			case 244:
			case 323:
				//无碰撞体积的板
			case 78:
			case 70:
			case 72:
			case 147:
			case 148:

				//透明方块
				return "air";
				break;
			case 8:
			case 9:
				//水
				return "water";
				break;
			case 10:
			case 11:
				//岩浆
				return "lava";
				break;
			case 44:
			case 158:
				//半砖
				if($damage >= 8){
					return "block";
				}else{
					return "half";
				}
				break;
			case 64:
				//门
				//var_dump($damage." ");
				//TODO 不知如何判断门是否开启，因为以下条件永远满足
				if($block->isOpened()){
					return "air";
				}else{
					return "block";
				}
				break;
			case 85:
			case 107:
			case 139:
				//1.5格高的无法跳跃物
				return "high";
				break;
			case 65:
			case 106:
				//可攀爬物
				return "climb";
				break;
			default:
				//普通方块
				return "block";
				break;
		}
	}

	public function MobDeath(EntityDeathEvent $event){
		//var_dump("death");
		$entity = $event->getEntity();
		if($entity instanceof Zombie){
			$eid = $entity->getID();
			if(isset($this->zombie[$eid])){
				unset($this->zombie[$eid]);
			}
		}
		if($entity instanceof Creeper){
			$eid = $entity->getID();
			if(isset($this->Creeper[$eid])){
				unset($this->Creeper[$eid]);
			}
		}
	}

	/**
	 * 刷僵尸计时器
	 */
	public function MobGenerate(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			//$this->server->getLogger()->info("准备生成僵尸");
			$level = $p->getLevel();
			$max = 15;
			//if ($level->getTime() >= 13500) {  //是夜晚
			//$this->server->getLogger()->info("时间OK");
			$v3 = new Vector3($p->getX() + mt_rand(-$this->birth_r, $this->birth_r), $p->getY(), $p->getZ() + mt_rand(-$this->birth_r, $this->birth_r));
			for($y0 = $p->getY() - 10; $y0 <= $p->getY() + 10; $y0++){
				$v3->y = $y0;
				if($this->whatBlock($level, $v3) == "block"){
					//$this->server->getLogger()->info("方块OK");
					$v3_1 = $v3;
					$v3_1->y = $y0 + 1;
					$v3_2 = $v3;
					$v3_2->y = $y0 + 2;
					$random = mt_rand(0, 1);


					if($level->getBlock($v3_1)->getID() == 0 and $level->getBlock($v3_2)->getID() == 0){  //找到地面
						/** @var Entity[] $zoC */
						$zoC = [];
						/** @var Entity[] $cowc */
						$cowc = [];
						foreach($level->getEntities() as $zo){
							if($zo instanceof Zombie) $zoC[] = $zo;
							if($zo instanceof Cow) $cowc[] = $zo;
						}


						if(count($zoC) > $max){
							for($i = 0; $i < (count($zoC) - $max); $i++) $zoC[$i]->kill();
						}elseif($random == 0 && $level->getTime() >= 13500){
							$pos = new Position($v3->x, $v3->y, $v3->z, $level);

							$this->server->getPluginManager()->callEvent($ev = new EntityGenerateEvent($pos, Zombie::NETWORK_ID, EntityGenerateEvent::CAUSE_AI_HOLDER));
							if(!$ev->isCancelled()){
								$this->spawnZombie($ev->getPosition());
							}
							//$this->server->getLogger()->info("生成1僵尸");
						}

						if(count($cowc) > $max){
							for($i = 0; $i < (count($cowc) - $max); $i++) $cowc[$i]->kill();
						}elseif($random == 1){
							$pos = new Position($v3->x, $v3->y, $v3->z, $level);

							$this->server->getPluginManager()->callEvent($ev = new EntityGenerateEvent($pos, Cow::NETWORK_ID, EntityGenerateEvent::CAUSE_AI_HOLDER));
							if(!$ev->isCancelled()){
								$this->spawnCow($ev->getPosition());
							}
							//$this->server->getLogger()->info("生成1牛");
						}
						break;
					}
				}
			}
		}
	}

	public function EntityDamage(EntityDamageEvent $event){  //击退修复
		if($event instanceof EntityDamageByEntityEvent){
			$p = $event->getDamager();
			$entity = $event->getEntity();
			if($entity instanceof Zombie){
				$array = &$this->zombie;
			}elseif($entity instanceof Creeper){
				$array = &$this->Creeper;
			}elseif($entity instanceof Cow){
				$array = &$this->Cow;
			}elseif($entity instanceof Pig){
				$array = &$this->Pig;
			}elseif($entity instanceof Sheep){
				$array = &$this->Sheep;
			}elseif($entity instanceof Chicken){
				$array = &$this->Chicken;
			}elseif($entity instanceof Skeleton){
				$array = &$this->Skeleton;
			}else{
				$array = [];
			}
			if(isset($array[$entity->getId()])){
				if($p instanceof Player and ($array[$entity->getId()]['canAttack'] == 0)){
					$weapon = $p->getInventory()->getItemInHand()->getID();  //得到玩家手中的武器
					$high = 0;
					if($weapon == 258 or $weapon == 271 or $weapon == 275){  //击退x5
						$back = 1.5;
					}elseif($weapon == 267 or $weapon == 272 or $weapon == 279 or $weapon == 283 or $weapon == 286){  //击退x1
						$back = 3;
					}elseif($weapon == 276){  //击退x2
						$back = 4;
					}elseif($weapon == 292){  //击退x10
						$back = 8;
						$high = 3;
					}else{
						$back = 1;
					}
					//var_dump("玩家".$p->getName()."攻击了ID为".$zo->getId()."的实体");
					$array[$entity->getId()]['x'] = $array[$entity->getId()]['x'] - $array[$entity->getId()]['xxx'] * $back;
					$array[$entity->getId()]['y'] = $entity->getY() + $high;
					$array[$entity->getId()]['z'] = $array[$entity->getId()]['z'] - $array[$entity->getId()]['zzz'] * $back;
					$pos = new Vector3 ($array[$entity->getId()]['x'], $array[$entity->getId()]['y'], $array[$entity->getId()]['z']);  //目标坐标
					//$entity->setPosition($pos);
					$entity->knockBack($entity, 0, $array[$entity->getId()]['xxx'] * $back, $array[$entity->getId()]['zzz'] * $back);
					if(isset($array[$entity->getId()])){
						$zom = &$array[$entity->getId()];
						$zom['IsChasing'] = $p->getName();
						//var_dump( $zom['IsChasing']);
					}
				}
			}
		}
	}

	public function knockBackover(Entity $entity, Vector3 $v3){
		if($entity instanceof Entity){
			if(isset($this->zombie[$entity->getId()])){
				$entity->setPosition($v3);
				$this->zombie[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Cow[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Cow[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Pig[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Pig[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Sheep[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Sheep[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Chicken[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Chicken[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Skeleton[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Skeleton[$entity->getId()]['knockBack'] = false;
			}
			if(isset($this->Creeper[$entity->getId()])){
				$entity->setPosition($v3);
				$this->Creeper[$entity->getId()]['knockBack'] = false;
			}
		}
	}

}

