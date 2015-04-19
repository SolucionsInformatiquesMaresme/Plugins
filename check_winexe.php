#!/usr/bin/php
<?php

$bin = "/local/bin/winexe-static-081123";
$user = "";
$pass = "";
$mode = "";
$domain = "";
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
	if (strpos($arg, "d=") !== false) { $domain = str_replace("d=","",$arg); }
	
	if (strpos($arg, "s=") !== false) { $winexec_script = str_replace("s=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_winexe.php Version 0.1.0\n";
	echo "check_winexe.php -h for help.\n";
}

if ($help == true) {
	echo "check_winexe.php\n";
	echo "Format out (local scripts)\n\n";
	echo "#result=STATUS UNITS VALUE\n";
	echo "STATUS\n";
	echo "\t0=Ok\n";
	echo "\t1=Warning\n";
	echo "\t2=Critical\n";
	echo "\t3=Unknown\n\n";
	echo "UNITS = string with name units, example: Mb, Errors, Status, Times\n\n";
	echo "VALUE = integer with value for graphics, example: 15, 14.5, 1234\n\n";
	echo "Examples:\n";
	echo "\tresult=0 errors 2 (status is OK with 2 errors)\n";
	echo "\tresult=2 Mb 15 (status is CRITICAL with 15 MB\n";
}

if (($help == false) and ($version == false)) {
	$command = $bin." -U ".$domain."/".$user."%".$pass." //".$host." \"".$winexec_script."\" | grep \"result=\"";
	$value = shell_exec($command);
	$value = str_replace("\n","",$value);
	$value = str_replace("result=","",$value);
	$value = str_replace(" ",";",$value);
		$salida = "";
		$unidades = "";
		$valor_unidades = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == ";") { 
				$variable++;
			} else {
				if ($variable == 1) { $salida .= $value[$i]; }
				if ($variable == 2) { $unidades .= $value[$i]; }
				if ($variable == 3) { $valor_unidades .= $value[$i]; }
			}
		}
		$salida = $salida * 1;
		$valor_unidades = $valor_unidades * 1;
		if ($salida == 0) { $salida_string = "OK - "; }
		if ($salida == 1) { $salida_string = "WARNING - "; }
		if ($salida == 2) { $salida_string = "CRITICAL - "; }
		if ($salida == 3) { $salida_string = "UNKNOWN - "; }
		echo $salida_string."Result remote script is ".$salida." with ".$unidades." >> ".$valor_unidades." | ".$unidades."=".$valor_unidades."\n";

}

exit($salida);


?>

