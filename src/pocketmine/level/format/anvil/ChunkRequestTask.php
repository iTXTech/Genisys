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

namespace pocketmine\level\format\anvil;

use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\utils\BinaryStream;


class ChunkRequestTask extends AsyncTask{

	protected $levelId;

	protected $chunk;
	protected $chunkX;
	protected $chunkZ;

	protected $tiles;

	public function __construct(Level $level, Chunk $chunk){
		$this->levelId = $level->getId();

		$this->chunk = $chunk->toFastBinary();
		$this->chunkX = $chunk->getX();
		$this->chunkZ = $chunk->getZ();

		$tiles = "";
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		foreach($chunk->getTiles() as $tile){
			if($tile instanceof Spawnable){
				$nbt->setData($tile->getSpawnCompound());
				$tiles .= $nbt->write(true);
			}
		}

		$this->tiles = $tiles;
	}

	public function onRun(){

		$chunk = Chunk::fromFastBinary($this->chunk);
		$extraData = new BinaryStream();
		$extraData->putLInt(count($chunk->getBlockExtraDataArray()));
		foreach($chunk->getBlockExtraDataArray() as $key => $value){
			$extraData->putLInt($key);
			$extraData->putLShort($value);
		}

		$ordered = $chunk->getBlockIdArray() .
			$chunk->getBlockDataArray() .
			$chunk->getBlockSkyLightArray() .
			$chunk->getBlockLightArray() .
			pack("C*", ...$chunk->getHeightMapArray()) .
			pack("N*", ...$chunk->getBiomeColorArray()) .
			$extraData->getBuffer() .
			$this->tiles;

		$this->setResult($ordered, false);
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level instanceof Level and $this->hasResult()){
			$level->chunkRequestCallback($this->chunkX, $this->chunkZ, $this->getResult(), FullChunkDataPacket::ORDER_LAYERED);
		}
	}

}