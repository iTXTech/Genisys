<?php

namespace synapse\network\protocol\spp;

class FastPlayerListPacket extends DataPacket{
	const NETWORK_ID = Info::FAST_PLAYER_LIST_PACKET;

	const TYPE_ADD = 0;
	const TYPE_REMOVE = 1;

	/** @var UUID */
	public $sendTo;
	//REMOVE: UUID, ADD: UUID, entity id, name
	/** @var array[] */
	public $entries = [];
	public $type;

	/*public function clean(){
		$this->entries = [];
		return parent::clean();
	}*/

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putUUID($this->sendTo);
		$this->putByte($this->type);
		$this->putInt(count($this->entries));
		foreach($this->entries as $d){
			if($this->type === self::TYPE_ADD){
				$this->putUUID($d[0]);
				$this->putLong($d[1]);
				$this->putString($d[2]);
			}else{
				$this->putUUID($d[0]);
			}
		}
	}

}
