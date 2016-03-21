<?php
$server = proc_open(PHP_BINARY . " src/pocketmine/PocketMine.php --no-wizard --disable-readline", [
	0 => ["pipe", "r"],
	1 => ["pipe", "w"],
	2 => ["pipe", "w"]
], $pipes);
fwrite($pipes[0], "version\nms\nstop\n\n");
while(!feof($pipes[1])){
	echo $con = fgets($pipes[1]);
	if(strpos($con, "stopped") > 0){
		fclose($pipes[0]);
		fclose($pipes[1]);
		fclose($pipes[2]);
		$ret = proc_close($server);
		break;
	}
}
if(!isset($ret)) $ret = proc_close($server);
echo "\n\nReturn value: ". $ret ."\n";
if(count(glob("plugins/PocketMine-iTX/Genisys*.phar")) === 0){
	echo "No server phar created!\n";
	exit(1);
}else{
	exit(0);
}
