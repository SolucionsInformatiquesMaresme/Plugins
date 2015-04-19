#!/usr/bin/php
<?php

$host = "";
$user = "nagios";
$pass = "";
$domain = "";
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
        if (strpos($arg, "d=") !== false) { $domain = str_replace("d=","",$arg); }

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
	echo "check_wmi.php Version 0.1.0\n";
	echo "check_wmi.php -h for help.\n";
}

if ($help == true) {
	echo "check_wmi.php Version 0.1.0\n\n";
	echo "Requirements: wmic\n\n";
}


if (($version == false) and ($help == false) and ($mode != "")) {
	if ($pass != "") {	
		$connect = "wmic -U ".$domain."/".$user."%".$pass." ";
	}

	function command($command,$connect,$host) {
		$result = "";
		$query = $connect."//".$host." '".$command."' | tail -n1";
		$result = shell_exec($query);
		if ($result == "") {
			echo "UNKNOWN - Connection is not possible or command no exist on ".$host." host.";
			$salida = 3;
			exit($salida);
		}
		return $result;
	}

	if ($mode == "poolnonpaged") {
		$command = "select PoolNonpagedBytes from Win32_PerfRawData_PerfOS_Memory";
		$value = command($command,$connect,$host);
		$memory_used = str_replace("\n",";",$value);
		$command = "select TotalPhysicalMemory from Win32_ComputerSystem";
		$value = command($command,$connect,$host);

		$memory_total = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value); $i++) {
			if ($value[$i] == "|") { $variable++; }
			if (($value[$i] != "|") AND ($variable == 2)) { $memory_total .= $value[$i]; }
		}
		$memory_total = str_replace("\n",";",$memory_total);
		$value_out = round(((($memory_used/($memory_total/100))*100)),2);
		$string_out = "PoolNonpagedBytes used % >> ".$value_out;
		$pnp_out = "used=".$value_out.";".$warning.";".$critical;
		
	}






	if ($warning != "") { if ($value_out >= $warning) { $salida = 1; $string_salida = "WARNING - "; } }
	if ($critical != "") { if ($value_out >= $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
	if ($warning_low != "") { if ($value_out <= $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
	if ($critical_low != "") { if ($value_out <= $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
	if ($pnp_out == "") {
		echo $string_salida.$string_out."\n";
	} else {
		echo $string_salida.$string_out." | ".$pnp_out."\n";
	}
}

exit($salida)


?>