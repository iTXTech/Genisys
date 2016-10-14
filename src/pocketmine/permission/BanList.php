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

namespace pocketmine\permission;

use pocketmine\Server;
use pocketmine\utils\MainLogger;

class BanList{

	/** @var BanEntry[] */
	private $list = [];

	/** @var string */
	private $file;

	/** @var bool */
	private $enabled = true;

	/**
	 * @param string $file
	 * @param bool $isYaml
	 */
	public function __construct($file){
		$this->file = $file;
	}

	/**
	 * @return bool
	 */
	public function isEnabled(){
		return $this->enabled === true;
	}

	/**
	 * @param bool $flag
	 */
	public function setEnabled($flag){
		$this->enabled = (bool) $flag;
	}

	/**
	 * @return BanEntry[]
	 */
	public function getEntries(){
		$this->removeExpired();

		return $this->list;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isBanned($name){
		$name = strtolower($name);
		if(!$this->isEnabled()){
			return false;
		}else{
			$this->removeExpired();

			return isset($this->list[$name]);
		}
	}

	/**
	 * @param BanEntry $entry
	 */
	public function add(BanEntry $entry){
		$this->list[$entry->getName()] = $entry;
		$this->save();
	}

	/**
	 * @param string    $target
	 * @param string    $reason
	 * @param \DateTime $expires
	 * @param string    $source
	 *
	 * @return BanEntry
	 */
	public function addBan($target, $reason = null, $expires = null, $source = null){
		$entry = new BanEntry($target);
		$entry->setSource($source != null ? $source : $entry->getSource());
		$entry->setExpires($expires);
		$entry->setReason($reason != null ? $reason : $entry->getReason());

		$this->list[$entry->getName()] = $entry;
		$this->save();

		return $entry;
	}

	/**
	 * @param string $name
	 */
	public function remove($name){
		$name = strtolower($name);
		if(isset($this->list[$name])){
			unset($this->list[$name]);
			$this->save();
		}
	}

	public function removeExpired(){
		foreach($this->list as $name => $entry){
			if($entry->hasExpired()){
				unset($this->list[$name]);
			}
		}
	}

	public function load(){
		$this->list = [];
		$content = file_get_contents($this->file);
		$data = yaml_parse($content);
		if(is_array($data)){
			foreach($data as $array){
				$entry = BanEntry::fromArray($array);
				if($entry instanceof BanEntry){
					$this->list[$entry->getName()] = $entry;
				}
			}
		}
	}

	public function save($flag = true){
		$this->removeExpired();
		$fp = @fopen($this->file, "w");
		if(is_resource($fp)){
			if($flag === true){
				fwrite($fp, "# Updated " . strftime("%x %H:%M", time()) . " by " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion() . "\n");
				fwrite($fp, "# victim name | ban date | banned by | banned until | reason\n\n");
			}

			$data = [];
			foreach($this->list as $entry){
				$data[] = $entry->getArray();
			}

			fwrite($fp, yaml_emit($data));
			fclose($fp);
		}else{
			MainLogger::getLogger()->error("Could not save ban list");
		}
	}

	public static function __toYaml($old_file, $new_file) {
		$fp1 = @fopen($old_file, "r");
		$fp2 = @fopen($new_file, "w");
		if(is_resource($fp1) && is_resource($fp2)){
			$data = [];
			while(($line = fgets($fp1)) !== false){
				if($line{0} !== "#"){
					if(strlen($line) >= 2){
						$str = explode("|", trim($line));
						$entry = BanEntry::fromArray($str);
						$data[] = $entry->getArray();
					}
				}
			}
			fclose($fp1);

			fwrite($fp2, yaml_emit($data));
			fclose($fp2);

			return true;
		}else{
			return false;
		}
	}

}