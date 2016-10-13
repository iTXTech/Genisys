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
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;

class BanList{

	/** @var BanEntry[] */
	private $list = [];

	/** @var string */
	private $file;

	/** @var Config */
	private $config;

	/** @var bool */
	private $enabled = true;

	/**
	 * @param string $file
	 */
	public function __construct($file){
		$this->file = $file;
		$this->config = new Config($file, Config::YAML);
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
		foreach ($this->config->getAll() as $data){
			$entry = BanEntry::fromArray($data);
			if($entry instanceof BanEntry){
				$this->list[$entry->getName()] = $entry;
			}
		}
	}

	public function save($flag = true){
		$this->removeExpired();
		$data = [];
		foreach($this->list as $entry){
			$data[] = $entry->getArray();
		}
		$this->config->setAll($data);
		$this->config->save();
	}

}
