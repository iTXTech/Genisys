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

namespace pocketmine\utils;

use LogLevel;
use pocketmine\Thread;
use pocketmine\Worker;

class MainLogger extends \AttachableThreadedLogger{
	protected $logFile;
	protected $logStream;
	protected $shutdown;
	protected $logDebug;
	private $logResource;
	/** @var MainLogger */
	public static $logger = null;
	
	private $consoleCallback;

	/** Extra Settings */
	protected $write = false;

	public $shouldSendMsg = "";
	public $shouldRecordMsg = false;
	private $lastGet = 0;

	public function setSendMsg($b){
		$this->shouldRecordMsg = $b;
		$this->lastGet = time();
	}

	public function getMessages(){
		$msg = $this->shouldSendMsg;
		$this->shouldSendMsg = "";
		$this->lastGet = time();
		return $msg;
	}

	/**
	 * @param string $logFile
	 * @param bool   $logDebug
	 *
	 * @throws \RuntimeException
	 */
	public function __construct($logFile, $logDebug = false){
		if(static::$logger instanceof MainLogger){
			throw new \RuntimeException("MainLogger has been already created");
		}
		static::$logger = $this;
		touch($logFile);
		$this->logFile = $logFile;
		$this->logDebug = (bool) $logDebug;
		$this->logStream = new \Threaded;
		$this->start();
	}

	/**
	 * @return MainLogger
	 */
	public static function getLogger(){
		return static::$logger;
	}

	public function emergency($message, $name = "EMERGENCY"){
		$this->send($message, \LogLevel::EMERGENCY, $name, TextFormat::RED);
	}

	public function alert($message, $name = "ALERT"){
		$this->send($message, \LogLevel::ALERT, $name, TextFormat::RED);
	}

	public function critical($message, $name = "CRITICAL"){
		$this->send($message, \LogLevel::CRITICAL, $name, TextFormat::RED);
	}

	public function error($message, $name = "ERROR"){
		$this->send($message, \LogLevel::ERROR, $name, TextFormat::DARK_RED);
	}

	public function warning($message, $name = "WARNING"){
		$this->send($message, \LogLevel::WARNING, $name, TextFormat::YELLOW);
	}

	public function notice($message, $name = "NOTICE"){
		$this->send($message, \LogLevel::NOTICE, $name, TextFormat::AQUA);
	}

	public function info($message, $name = "INFO"){
		$this->send($message, \LogLevel::INFO, $name, TextFormat::WHITE);
	}

	public function debug($message, $name = "DEBUG"){
		if($this->logDebug === false){
			return;
		}
		$this->send($message, \LogLevel::DEBUG, $name, TextFormat::GRAY);
	}

	/**
	 * @param bool $logDebug
	 */
	public function setLogDebug($logDebug){
		$this->logDebug = (bool) $logDebug;
	}

	public function logException(\Throwable $e, $trace = null){
		if($trace === null){
			$trace = $e->getTrace();
		}
		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$errorConversion = [
			0 => "EXCEPTION",
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED",
		];
		if($errno === 0){
			$type = LogLevel::CRITICAL;
		}else{
			$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? LogLevel::WARNING : LogLevel::NOTICE);
		}
		$errno = isset($errorConversion[$errno]) ? $errorConversion[$errno] : $errno;
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}
		$errfile = \pocketmine\cleanPath($errfile);
		$this->log($type, get_class($e) . ": \"$errstr\" ($errno) in \"$errfile\" at line $errline");
		foreach(@\pocketmine\getTrace(1, $trace) as $i => $line){
			$this->debug($line);
		}
	}

	public function log($level, $message){
		switch($level){
			case LogLevel::EMERGENCY:
				$this->emergency($message);
				break;
			case LogLevel::ALERT:
				$this->alert($message);
				break;
			case LogLevel::CRITICAL:
				$this->critical($message);
				break;
			case LogLevel::ERROR:
				$this->error($message);
				break;
			case LogLevel::WARNING:
				$this->warning($message);
				break;
			case LogLevel::NOTICE:
				$this->notice($message);
				break;
			case LogLevel::INFO:
				$this->info($message);
				break;
			case LogLevel::DEBUG:
				$this->debug($message);
				break;
		}
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	protected function send($message, $level, $prefix, $color){
		$now = time();

		$thread = \Thread::getCurrentThread();
		if($thread === null){
			$threadName = "Server thread";
		}elseif($thread instanceof Thread or $thread instanceof Worker){
			$threadName = $thread->getThreadName() . " thread";
		}else{
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		if($this->shouldRecordMsg){
			if((time() - $this->lastGet) >= 10) $this->shouldRecordMsg = false; // 10 secs timeout
			else{
				if(strlen($this->shouldSendMsg) >= 10000) $this->shouldSendMsg = "";
				$this->shouldSendMsg .= $color . "|" . $prefix . "|" . trim($message, "\r\n") . "\n";
			}
		}

		$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("H:i:s", $now) . "] " . TextFormat::RESET . $color . "[" . $threadName . "/" . $prefix . "]:" . " " . $message . TextFormat::RESET);
		//$message = TextFormat::toANSI(TextFormat::AQUA . "[" . date("H:i:s") . "] ". TextFormat::RESET . $color ."<".$prefix . ">" . " " . $message . TextFormat::RESET);
		$cleanMessage = TextFormat::clean($message);

		if(!Terminal::hasFormattingCodes()){
			echo $cleanMessage . PHP_EOL;
		}else{
			echo $message . PHP_EOL;
		}

		if(isset($this->consoleCallback)){
			call_user_func($this->consoleCallback);
		}

		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->call($level, $message);
		}

		$this->logStream[] = date("Y-m-d", $now) . " " . $cleanMessage . "\n";
		if($this->logStream->count() === 1){
			$this->synchronized(function(){
				$this->notify();
			});
		}
	}

	/*public function run(){
		$this->shutdown = false;
		if($this->write){
			$this->logResource = fopen($this->logFile, "a+b");
			if(!is_resource($this->logResource)){
				throw new \RuntimeException("Couldn't open log file");
			}

			while($this->shutdown === false){
				if(!$this->write) {
					fclose($this->logResource);
					break;
				}
				$this->synchronized(function(){
					while($this->logStream->count() > 0){
						$chunk = $this->logStream->shift();
						fwrite($this->logResource, $chunk);
					}

					$this->wait(25000);
				});
			}

			if($this->logStream->count() > 0){
				while($this->logStream->count() > 0){
					$chunk = $this->logStream->shift();
					fwrite($this->logResource, $chunk);
				}
			}

			fclose($this->logResource);
		}
	}*/

	public function run(){
		$this->shutdown = false;
		while($this->shutdown === false){
			$this->synchronized(function(){
				while($this->logStream->count() > 0){
					$chunk = $this->logStream->shift();
					if($this->write){
						$this->logResource = file_put_contents($this->logFile, $chunk, FILE_APPEND);
					}
				}

				$this->wait(200000);
			});
		}

		if($this->logStream->count() > 0){
			while($this->logStream->count() > 0){
				$chunk = $this->logStream->shift();
				if($this->write){
					$this->logResource = file_put_contents($this->logFile, $chunk, FILE_APPEND);
				}
			}
		}
	}

	public function setWrite($write){
		$this->write = $write;
	}
	
	public function setConsoleCallback($callback){
		$this->consoleCallback = $callback;
	}
}
