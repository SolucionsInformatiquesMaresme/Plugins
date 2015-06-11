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
		$query = $connect."//".$host." '".$command."'";
		$result = shell_exec($query);
		if ($result == "") {
			echo "UNKNOWN - Connection is not possible or command no exist on ".$host." host.";
			$salida = 3;
			exit($salida);
		}
		return $result;
	}

	if ($mode == "volums") {
		$command = "select description,PercentFreeSpace,PercentFreeSpace_Base from Win32_PerfRawData_PerfDisk_LogicalDisk";
		$value = command($command,$connect,$host);
		$warnings = 0;
		$criticals = 0;
		$revised = 0;
		$alarma = false;
		$salida_equipos = "";
		$description = "";
		$mb_used = "";
		$mb_total = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == "\n") {
				if ((strpos($description,$option)) !== FALSE) {
					$per = round((($mb_used*100)/$mb_total),2);
					if (($per >= $warning) and ($per < $critical)) { $warnings++; $alarma = true; }
					if ($per >= $critical) { $criticals++; $alarma = true; }
					if ($alarma == true) {
						if ($salida_equipos != "") { $salida_equipos .= ", "; }
						$salida_equipos .= $description." (".$per."%)";
					}
					$revised++;
				}
				$alarma = false;
				$description = "";
				$mb_used = "";
				$mb_total = "";
				$variable = 1;
			} else {
				if ($value[$i] == "|") { $variable++; }
				if (($value[$i] != "|") AND ($variable == 2)) { $description .= $value[$i]; }
				if (($value[$i] != "|") AND ($variable == 3)) { $mb_used .= $value[$i]; }
				if (($value[$i] != "|") AND ($variable == 4)) { $mb_total .= $value[$i]; }
			}
		}
		if ($warnings > 0) { $string_salida = "WARNING - "; $salida = 1; }
		if ($criticals > 0) { $string_salida = "CRITICAL - "; $salida = 2; }
		$salida_total = $warnings."/".$revised." with state warning (>".$warning."%) and ".$criticals."/".$revised." with state critical (>".$critical."%)";
		$pnp_out = "counter_warnings=".$warnings." counter_critical=".$criticals;
		echo $string_salida.$salida_total." < ".$salida_equipos." > | ".$pnp_out."\n";
	}


}

exit($salida)


?>