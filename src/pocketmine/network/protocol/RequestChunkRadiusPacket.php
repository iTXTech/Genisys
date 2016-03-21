<?php
/**
 * Author: PeratX
 * OpenGenisys Project
 */
namespace pocketmine\network\protocol;

class RequestChunkRadiusPacket extends DataPacket{
	const NETWORK_ID = Info::REQUEST_CHUNK_RADIUS_PACKET;

	public $radius;

	public function decode(){
		$this->radius = $this->getInt();
	}

	public function encode(){

	}
}