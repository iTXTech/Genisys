<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */
 
namespace synapse\event\synapse;

use synapse\Synapse;

class SynapseDisconnectEvent extends SynapseEvent{
	public static $handlerList = null;

	/** @var int */
	protected $type;

	/** @var string */
	protected $reason;

	public function __construct(Synapse $synapse, int $type, string $reason){
		$this->synapse = $synapse;
		$this->type = $type;
		$this->reason = $reason;
	}

	public function getType() : int{
		return $this->type;
	}

	public function getReason() : string{
		return $this->reason;
	}
}
