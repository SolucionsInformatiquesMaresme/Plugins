#!/usr/bin/php
<?php

$host = "";
$help = false;
$version = false;
$warning_low = -1;
$critical_low = -1;
$salida = 2;
$string_salida = "CRITICAL - ";
$string_out = "";

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "t=") !== false) { $comunity = str_replace("t=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }
	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_oki_levels.php Version 0.1.0\n";
	echo "check_oki_levels.php -h for help.\n";
}

if ($help == true) {
	echo "check_oki_levels.php Version 0.1.0\n\n";
#	echo "Requirements: sshpass\n\n";
#	echo "With SSL Certificate installed:\n";
}

$warnings = 0;
$criticals = 0;
if (($version == false) and ($help == false)) {
	$lista = 1;
	$lista_consumibles = snmpwalk($host,$comunity,"1.3.6.1.2.1.43.11.1.1.6.1");
	if ($lista_consumibles != false) {
	foreach ($lista_consumibles as $val) {
		$consumible = str_replace("STRING: ","",$val);
		$consumible = str_replace(chr(34),"",$consumible);
		$consumbile_total = snmpget($host,$comunity,"1.3.6.1.2.1.43.11.1.1.8.1.".$lista); 
		$consumbile_total = str_replace("INTEGER: ","",$consumbile_total);
		$consumbile_total = str_replace(chr(34),"",$consumbile_total);
		$consumbile_usado = snmpget($host,$comunity,"1.3.6.1.2.1.43.11.1.1.9.1.".$lista); 
		$consumbile_usado = str_replace("INTEGER: ","",$consumbile_usado);
		$consumbile_usado = str_replace(chr(34),"",$consumbile_usado);
		$perc = round((($consumbile_usado*100)/$consumbile_total),2);
		if ($lista > 1) { $string_out .= ", "; }
		$string_out .= $consumible." <".$perc."%>";
		if ($perc <= $warning_low) { $warnings++; }
		if ($perc <= $critical_low) { $criticals++; }
		$lista++;
	}
	}
	if (($warnings == 0) AND ($criticals == 0)) { $salida = 0; $string_salida = "OK - "; }
	if ($warnings > 0) { $salida = 1; $string_salida = "WARNING - "; }
	if ($criticals > 0) { $salida = 2; $string_salida = "CRITICAL - "; }
	echo $string_salida.$string_out."\n";
}
exit($salida)
?>