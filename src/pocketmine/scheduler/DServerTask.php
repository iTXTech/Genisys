<?php
/*
 * DServerTask 2.0
 * @author MUedsa, PeratX
*/
namespace pocketmine\scheduler;

use pocketmine\Server;

class DServerTask extends AsyncTask{

	public $data;
	public $autotimes;
	public $re;

	public function __construct($data, $autotimes = 5){
		$this->data = $data;
		$this->autotimes = $autotimes;
	}

	public function onRun(){
		$re = [0, 0];
		foreach($this->data as $d){
			$data = $this->getInfo($d);
			$re[0] = $re[0] + $data[0];
			$re[1] = $re[1] + $data[1];
		}
		$this->re = (array) $re;
	}


	public function getInfo($ds, $time = 1){
		$tmp = explode(":", $ds);
		$ip = $tmp[0];
		$port = $tmp[1];
		$client = stream_socket_client("udp://" . $ip . ":" . $port, $errno, $errstr);    //非阻塞Socket
		if($client){
			stream_set_timeout($client, 1);
			$Handshake_to = "\xFE\xFD" . chr(9) . pack("N", 233);
			fwrite($client, $Handshake_to);
			$Handshake_re_1 = fread($client, 65535);
			if($Handshake_re_1 != ""){
				$Handshake_re = $this->decode($Handshake_re_1);
				$Status_to = "\xFE\xFD" . chr(0) . pack("N", 233) . pack("N", $Handshake_re["payload"]);
				fwrite($client, $Status_to);
				$Status_re_1 = fread($client, 65535);
				if($Status_re_1 != ""){
					$Status_re = $this->decode($Status_re_1);
					$ServerData = explode("\x00", $Status_re["payload"]);
					return [$ServerData[3], $ServerData[4]];
				}
			}
			fclose($client);
		}
		if($time < $this->autotimes){
			return $this->getInfo($ds, $time + 1);
		}elseif($time = $this->autotimes) return [0, 0];
		return [0, 0];
	}

	public function onCompletion(Server $server){
		$re = $this->re;
		if($re[0] > 0) $server->dserverPlayers = $re[0];
		if($re[1] > 0) $server->dserverAllPlayers = $re[1];
		//$server->getNetwork()->updateName();
	}

	public function decode($buffer){
		$redata = [];
		$redata["packetType"] = ord($buffer{0});
		$redata["sessionID"] = unpack("N", substr($buffer, 1, 4))[1];
		$redata["payload"] = rtrim(substr($buffer, 5));
		return $redata;
	}

}