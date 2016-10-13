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

namespace pocketmine\block;

interface IndirectRedstoneSource{
	const POWER_MODE_ALL = 0;
	const POWER_MODE_ALL_EXCEPT_WIRE = 1;

	const REDSTONE_POWER_MIN = 0;
	const REDSTONE_POWER_MAX = 15;

	public function getIndirectRedstonePower(Block $block, int $face, int $powerMode) : int;

	public function hasIndirectRedstonePower(Block $block, int $face, int $powerMode) : bool;

	public function getRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : int;

	public function hasRedstonePower(Block $block, int $powerMode = self::POWER_MODE_ALL) : bool;

	public function isRedstoneConductor() : bool;
}