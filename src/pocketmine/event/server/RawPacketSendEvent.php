<?php

namespace pocketmine\event\server;

use pocketmine\event;
use pocketmine\event\Cancellable;

class RawPacketSendEvent extends ServerEvent implements Cancellable {
	public static $handlerList = null;
	private $payload;
	private $address;
	private $port;
	public function __construct(string $address, int $port, string $payload) {
		$this->payload = $payload;
		$this->address = $address;
		$this->port = $port;
	}
	public function getPayload() {
		return $this->payload;
	}
	public function getPort() {
		return $this->port;
	}
	public function getAddress() {
		return $this->address;
	}
}