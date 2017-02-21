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

declare(strict_types = 1);

namespace pocketmine\command;

use pocketmine\Thread;


class CommandReader extends Thread{

	const TYPE_READLINE = 0;
	const TYPE_STREAM = 1;
	const TYPE_PIPED = 2;

	/** @var \Threaded */
	protected $buffer;
	private $shutdown = false;

	private $type = self::TYPE_STREAM;

	public function __construct(){
		$this->buffer = new \Threaded;

		$opts = getopt("", ["disable-readline"]);
		if((extension_loaded("readline") and !isset($opts["disable-readline"]) and !$this->isPipe(STDIN))){
			$this->type = self::TYPE_READLINE;
		}

		$this->start();
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	public function quit(){
		$wait = microtime(true) + 0.5;
		while(microtime(true) < $wait){
			if($this->isRunning()){
				usleep(100000);
			}else{
				parent::quit();
				return;
			}
		}

		$message = "Thread blocked for unknown reason";
		if($this->type === self::TYPE_PIPED){
			$message = "STDIN is being piped from another location and the pipe is blocked, cannot stop safely";
		}

		throw new \ThreadException($message);
	}

	private function initStdin(){
		global $stdin;

		if(is_resource($stdin)){
			fclose($stdin);
		}

		$stdin = fopen("php://stdin", "r");
		if($this->isPipe($stdin)){
			$this->type = self::TYPE_PIPED;
		}else{
			$this->type = self::TYPE_STREAM;
		}
	}

	/**
	 * Checks if the specified stream is a FIFO pipe.
	 *
	 * @param resource $stream
	 * @return bool
	 */
	private function isPipe($stream) : bool{
		return is_resource($stream) and ((function_exists("posix_isatty") and !posix_isatty($stream)) or ((fstat($stream)["mode"] & 0170000) === 0010000));
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 *
	 * @return bool if the main execution should continue reading lines
	 */
	private function readLine() : bool{
		$line = "";
		if($this->type === self::TYPE_READLINE){
			$line = trim(readline("> "));
			if($line !== ""){
				readline_add_history($line);
			}else{
				return true;
			}
		}else{
			global $stdin;

			if(!is_resource($stdin)){
				$this->initStdin();
			}

			switch($this->type){
				case self::TYPE_STREAM:
					$r = [$stdin];
					if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){ //nothing changed in 200000 microseconds
						return true;
					}elseif($count === false){ //stream error
						$this->initStdin();
					}

					if(($raw = fgets($stdin)) !== false){
						$line = trim($raw);
					}else{
						return false; //user pressed ctrl+c?
					}

					break;
				case self::TYPE_PIPED:
					if(($raw = fgets($stdin)) === false){ //broken pipe or EOF
						$this->initStdin();
						$this->synchronized(function(){
							$this->wait(200000);
						}); //prevent CPU waste if it's end of pipe
						return true; //loop back round
					}else{
						$line = trim($raw);
					}
					break;
			}
		}

		if($line !== ""){
			$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
		}

		return true;
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function run(){
		if($this->type !== self::TYPE_READLINE){
			$this->initStdin();
		}

		while(!$this->shutdown and $this->readLine());

		if($this->type !== self::TYPE_READLINE){
			global $stdin;
			fclose($stdin);
		}

	}

	public function getThreadName(){
		return "Console";
	}
}
