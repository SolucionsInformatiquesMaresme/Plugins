#!/usr/bin/php
<?php

$host = "";
$user = "nagios";
$pass = "";
$cert = "";
$mode = "";
$option = "";
$help = false;
$version = false;

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
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "u=") !== false) { $user = str_replace("u=","",$arg); }
	if (strpos($arg, "p=") !== false) { $pass = str_replace("p=","",$arg); }
	if (strpos($arg, "c=") !== false) { $cert = str_replace("c=","",$arg); }

	if (strpos($arg, "m=") !== false) { $mode = str_replace("m=","",$arg); }
	if (strpos($arg, "o=") !== false) { $option = str_replace("o=","",$arg); }
	if (strpos($arg, "s=") !== false) { $suboption = str_replace("s=","",$arg); }
	if (strpos($arg, "3=") !== false) { $option3 = str_replace("3=","",$arg); }

	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_linux.php Version 0.1.0\n";
	echo "check_linux.php -h for help.\n";
}

if ($help == true) {
	echo "check_linux.php Version 0.1.0\n\n";
}

	$texto_formateado = "";
	$value = shell_exec("cat /misc/produccionnas/monitoritzacio/ConsolaEvents/Scripts/MonitorSnapshots/Estat/MonitorSnapshots_Estat_VC_TMB_TFR.txt | sed '1,3 d'");
	$value = str_replace("\r","",$value);
	$value_1 = "";
	$value_2 = "";
	$variable = 1;
	for ($i = 0; $i < strlen($value); $i++) {
		if ($value[$i] == "\n") { 
			echo $value_1."|".$value_2."\n";
			$variable = 1;
			$value_1 = "";
			$value_2 = "";
			$x = 0;
		} else {
			if ($i>6) {
				if (($value[$i] == " ") and ($value[($i-1)] == " ") and ($value[($i-2)] == " ") and ($value[($i-3)] == " ") and ($variable ==1)) { $variable++; }
			}
			if ($value[$i] != " ") {
				if (($value[$i] != " ") AND ($variable == 1)) { $value_1 .= $value[$i]; }
				if (($value[$i] != " ") AND ($variable == 2)) { $value_2 .= $value[$i]; }
			}
		}
	}



#exit($salida)


?>