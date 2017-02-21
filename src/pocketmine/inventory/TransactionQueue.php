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

namespace pocketmine\inventory;

interface TransactionQueue{

	const DEFAULT_ALLOWED_RETRIES = 5;

	/**
	 * @return Inventory
	 */
	function getInventories();

	/**
	 * @return \SplQueue
	 */
	function getTransactions();

	/**
	 * @return int
	 */
	function getTransactionCount();

	/**
	 * @param Transaction $transaction
	 *
	 * Adds a transaction to the queue
	 */
	function addTransaction(Transaction $transaction);

	/**
	 * Handles transaction queue execution
	 */
	function execute();

}