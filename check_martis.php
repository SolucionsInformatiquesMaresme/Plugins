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
        if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }

        if (strpos($arg, "u=") !== false) { $url = str_replace("u=","",$arg); }
        if (strpos($arg, "s=") !== false) { $string = str_replace("s=","",$arg); }


        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
        if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
        if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }
}

	$result = shell_exec("curl -s ".$url);
	if ((strpos($result,$string)) !== FALSE) { 
		$string_out = "Existe el string ".$string." en la url <".$url.">";
	} else {
		$string_salida = "CRITICAL - ";
		$string_out = "NO existe el string ".$string." en la url <".$url.">";
		$salida = 2;
	}

	echo $string_salida.$string_out."\n";

exit($salida)


?>