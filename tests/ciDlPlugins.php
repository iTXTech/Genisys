<?php

/**
 * GETs an URL using cURL
 *
 * Source: src/pocketmine/utils/Utils.php
 *
 * @param     $page
 * @param int $timeout default 10
 * @param array $extraHeaders
 *
 * @return bool|mixed
 */
function getURL($page, $timeout = 10, array $extraHeaders = []){
	$ch = curl_init($page);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
			["User-Agent: Genisys-CI/1.0"], // required by GitHub API
			$extraHeaders));
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
	$ret = curl_exec($ch);
	curl_close($ch);

	return $ret;
}

function is_url($url){
	return filter_var($url, FILTER_VALIDATE_URL);
}

if(!defined("STDERR")){
	define("STDERR", fopen("php://stderr", "wt"));
}

define("PLUGINS_DIR", realpath($argv[1] ?? (dirname(__DIR__) . "/plugins/")) . "/");

$json = file_get_contents("php://stdin");
$data = json_decode($json);
if(!is_object($data)){
	fwrite(STDERR, "Failed to access GitHub API" . PHP_EOL);
	return;
}

if(isset($data->message, $data->documentation_url)){
	fwrite(STDERR, "GitHub API error: $data->message" . PHP_EOL);
	return;
}

if(!isset($data->body)){
	fwrite(STDERR, "GitHub API did not return expected value for pull request body" . PHP_EOL);
	return;
}

$body = $data->body;

foreach(explode("\n", $body) as $i => $line){
	$line = trim($line);
	if($line === "# Plugins to test with Travis-CI"){
		$started = true;
		continue;
	}
	if(!isset($started)){
		continue;
	}

	if($line === "<!-- END -->"){
		break;
	}
	if(strlen($line) === 0){
		break;
	}
	if(substr($line, 0, 5) === "<!-- "){
		continue;
	}

	unset($name);
	if(preg_match('%^\-[ \t]*(http(s)?://.*$)%i', $line, $match)){
		$url = $match[1];
	}elseif(preg_match('%^\-[ \t]*\[([^\]]+)\][ \t]*\(([^\)]+)\)$%i', $line, $match)){
		list(, $name, $url) = $match;
	}else{
		break;
	}
	if(!is_url($url)){
		break;
	}

	$plugin = getURL($url);
	if(!is_string($plugin)){
		fwrite(STDERR, "Failed to download plugin " . ($name ?? $url) . PHP_EOL);
		break;
	}

	if(isset($name)){
		file_put_contents(PLUGINS_DIR . $name, $plugin);
		continue;
	}

	if(strpos($plugin, "__HALT_COMPILER();") !== false){ // probably a phar
		file_put_contents($file = PLUGINS_DIR . "Genisys-CI-Untitled-$i.phar", $plugin);
		try{
			$phar = new Phar($file);
		}catch(UnexpectedValueException $e){
			rename($file, PLUGINS_DIR . "Genisys-CI-Untitled-$i.php");
		}
	}else{
		file_put_contents(PLUGINS_DIR . "Genisys-CI-Untitled-$i.php", $plugin);
	}
}

