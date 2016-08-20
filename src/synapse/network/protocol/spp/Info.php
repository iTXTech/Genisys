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

namespace synapse\network\protocol\spp;

class Info{
	const CURRENT_PROTOCOL = 6;

	const HEARTBEAT_PACKET = 0x01;
	const CONNECT_PACKET = 0x02;
	const DISCONNECT_PACKET = 0x03;
	const REDIRECT_PACKET = 0x04;
	const PLAYER_LOGIN_PACKET = 0x05;
	const PLAYER_LOGOUT_PACKET = 0x06;
	const INFORMATION_PACKET = 0x07;
	const TRANSFER_PACKET = 0x08;
	const BROADCAST_PACKET = 0x09;
	const FAST_PLAYER_LIST_PACKET = 0x0a;
}
