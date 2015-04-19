#!/usr/bin/php
<?php

$host = "";
$user = "nagios";
$pass = "";
$comunity = "";
$version = "";
$mode = "";
$oid = "";
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
	if (strpos($arg, "t=") !== false) { $comunity = str_replace("t=","",$arg); }
	if (strpos($arg, "v=") !== false) { $version = str_replace("v=","",$arg); }

	if (strpos($arg, "m=") !== false) { $mode = str_replace("m=","",$arg); }
	if (strpos($arg, "o=") !== false) { $oid = str_replace("o=","",$arg); }

	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_snmp.php Version 0.1.0\n";
	echo "check_snmp.php -h for help.\n";
}

if ($help == true) {
	echo "check_snmp.php Version 0.1.0\n\n";
}

if (($version == false) and ($help == false) and ($mode != "")) {
	if ($mode == "get") {
		$result = snmpget($host, $comunity, $oid);
		if ($result == "") {
			$tipo = "INDETERMINATE";
			$value_out = 0;
		} else {
			if ((strpos($result,"INTEGER:")) !== FALSE) { 
				$tipo = "INTEGER" ;
				$value_out = str_replace("INTEGER: ","",$result)*1;
				$pnp_out = $tipo."=".$value_out.";".$warning.";".$critical;
			}
			if ((strpos($result,"Counter32:")) !== FALSE) { 
				$tipo = "Counter32" ;
				$value_out = str_replace("Counter32: ","",$result)*1;
				$pnp_out = $tipo."=".$value_out.";".$warning.";".$critical;
			}
			if ((strpos($result,"STRING:")) !== FALSE) {
				$tipo = "STRING" ;
				$value_out = str_replace("STRING: ","",$result);
				$value_out = str_replace("\n","",$value_out);
				$value_out = str_replace("\t"," ",$value_out);
			}
			if ((strpos($result,"Timeticks:")) !== FALSE) {
				$tipo = "Timeticks" ;
				$value_out = str_replace("Timeticks: ","",$result);
				$value_out = str_replace("\n","",$value_out);
				$value_out = str_replace("\t"," ",$value_out);
			}
		}
		$string_out = "OID ".$oid." (".$tipo.") >> ".$value_out;
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