#!/opt/php/bin/php
<?php
$version = trim($_SERVER["argv"][1]);
$pocketmine = str_replace('const VERSION = "1.2dev', 'const VERSION = "1.2dev-' . $version, file_get_contents($file = "src/pocketmine/PocketMine.php"));
file_put_contents($file, $pocketmine);
$server = proc_open(PHP_BINARY . " src/pocketmine/PocketMine.php --no-wizard --disable-readline", [
	0 => ["pipe", "r"],
	1 => ["pipe", "w"],
	2 => ["pipe", "w"]
], $pipes);
sleep (5);
fwrite($pipes[0], "version\n");
sleep (5);
fwrite($pipes[0], "ms\n");
sleep (5);
fwrite($pipes[0], "stop\n");
while(!feof($pipes[1])){
	echo fgets($pipes[1]);
}
fclose($pipes[0]);
fclose($pipes[1]);
fclose($pipes[2]);
rename("/opt/data-2T/jenkins/jobs/Genisys-master/workspace/plugins/Genisys/Genisys_1.2dev-$version.phar","/opt/data-2T/jenkins/jobs/Genisys-master/workspace/artifact/Genisys_1.2dev-$version.phar");
if(file_exists("/opt/data-2T/jenkins/jobs/Genisys-master/workspace/artifact/Genisys_1.2dev-$version.phar")) exit (0);
exit (1);
