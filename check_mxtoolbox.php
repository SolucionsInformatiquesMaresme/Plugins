#!/usr/bin/php
<?php

$criticals = 0;
$warnings = 0;

$string = "";

$salida = 0;
$string_salida = "OK - ";

foreach ($argv as $arg) {
        if (strpos($arg, "m=") !== false) { $monitor = str_replace("m=","",$arg); }
        if (strpos($arg, "k=") !== false) { $key_api = str_replace("k=","",$arg); }
}

$url = "http://api.mxtoolbox.com/api/v1/Monitor/?authorization=".$key_api;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($ch);
curl_close($ch);
$json = json_decode($result, true);

for ($i=0; $i<sizeof($json); $i++) {
	if ($monitor == $json[$i]["Name"]) {
		$CurrentStatus = $json[$i]["CurrentStatus"];
		$MxRep = $json[$i]["MxRep"];
		$LastChecked = $json[$i]["LastChecked"];
	}
}

if ($CurrentStatus != 0) {
	$salida = 2;
	$string_salida = "CRITICAL - ";	
}
echo $string_salida."MxToolBox APIRest=".$monitor." MxRep=".$MxRep." (".$LastChecked.")\n";

exit($salida);

?>