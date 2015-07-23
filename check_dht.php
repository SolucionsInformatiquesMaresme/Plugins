#!/usr/bin/php
<?php

$warning = "";
$critical = "";
$warning_low = "";
$critical_low = "";

$valor_final = "";
$value_out = 0;
$pnp_out = "";

$salida = 0;
$string_salida = "OK - ";

$dato = "";

foreach ($argv as $arg) {
        if (strpos($arg, "d=") !== false) { $dato = str_replace("d=","",$arg); }
        if (strpos($arg, "f=") !== false) { $file = str_replace("f=","",$arg); }
        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
        if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
        if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }
}

$value = shell_exec("cat ".$file." | tail -1");


	$variable = 1;
	$fecha = "";
	$hora = "";
	$temperatura = "";
	$humedad = "";
	for ($i = 1; $i < strlen($value); $i++) {
		if ($value[$i] == " ") { $variable++; }
		if (($value[$i] != " ") AND ($variable == 1)) { $fecha .= $value[$i]; }
		if (($value[$i] != " ") AND ($variable == 2)) { $hora .= $value[$i]; }
		if (($value[$i] != " ") AND ($variable == 3)) { $temperatura .= $value[$i]; }
		if (($value[$i] != " ") AND ($variable == 4)) { $humedad .= $value[$i]; }
	}
	
$temperatura = $temperatura*1;
$humedad = $humedad*1;

if ($dato == "t") {
	$string_out = "Temperature is ".$temperatura." Celsius";
	$value_out = $temperatura;
	$pnp_out = "C=".$value_out.";".$warning.";".$critical;
}

if ($dato == "h") {
	$string_out = "Humidity is ".$humedad." %";
	$value_out = $humedad;
	$pnp_out = "%=".$value_out.";".$warning.";".$critical;
}

if ($warning != "") { if ($value_out > $warning) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical != "") { if ($value_out > $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
if ($warning_low != "") { if ($value_out < $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical_low != "") { if ($value_out < $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
echo $string_salida.$string_out." | ".$pnp_out;
exit($salida)

?>