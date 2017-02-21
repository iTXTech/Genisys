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

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\BlockEventPacket;

class NoteblockSound extends GenericSound{
	protected $instrument;
	protected $pitch;

	const INSTRUMENT_PIANO = 0;
	const INSTRUMENT_BASS_DRUM = 1;
	const INSTRUMENT_CLICK = 2;
	const INSTRUMENT_TABOUR = 3;
	const INSTRUMENT_BASS = 4;

	public function __construct(Vector3 $pos, $instrument = self::INSTRUMENT_PIANO, $pitch = 0){
		parent::__construct($pos, $instrument, $pitch);
		$this->instrument = $instrument;
		$this->pitch = $pitch;
	}

	public function encode(){
		$pk = new BlockEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->case1 = $this->instrument;
		$pk->case2 = $this->pitch;

		return $pk;
	}
}
