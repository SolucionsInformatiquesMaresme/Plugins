#!/usr/bin/php
<?php

$url = "";
$string = "";

$warning = "";
$critical = "";
$warning_low = "";
$critical_low = "";

$valor_final = "";
$value_out = 0;
$pnp_out = "";

$salida = 0;
$string_salida = "OK - ";

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $dbhost = str_replace("H=","",$arg); }
	if (strpos($arg, "u=") !== false) { $dbuname = str_replace("u=","",$arg); }
	if (strpos($arg, "p=") !== false) { $dbpass = str_replace("p=","",$arg); }
	if (strpos($arg, "d=") !== false) { $dbname = str_replace("d=","",$arg); }
	if (strpos($arg, "j=") !== false) { $job = str_replace("j=","",$arg); }
	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

$dbhandle = sybase_connect($dbhost, $dbuname, $dbpass) or die("Couldn't connect to sybase Server on $dbhost");
$db = sybase_select_db($dbname, $dbhandle) or die("Couldn't open database $myDB");

exit($salida)


?>