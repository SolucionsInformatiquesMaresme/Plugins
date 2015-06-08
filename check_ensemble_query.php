#!/usr/bin/php
<?php

$salida=0;
$warning = 0;
$critical = 0;
$query = "";
$dsn = "";
$string_salida = "OK - ";
$mensaje_salida = "";
$valor = "";
$nombre_fichero_tmp = "check_ensemble_tmp-".rand(9999999,99999999);

$warning = "";
$critical = "";
$warning_low = "";
$critical_low = "";

foreach ($argv as $arg) {
        if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
        if (strpos($arg, "d=") !== false) { $dsn = str_replace("d=","",$arg); }
        if (strpos($arg, "q=") !== false) { $query = str_replace("q=","",$arg); }		
        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
		if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
		if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }
}

if ($query == "") {
	$salida = 2;
	$estado = "CRITICAL";
	$mensaje_salida = "No se ha informado de valor query";
} else {
	if ($dsn == "") {
		$salida = 2;
		$estado = "CRITICAL";
		$mensaje_salida = "No existe el informado DSN";
	} else {
			## ABRIMOS EL FICHERO EN TMP Y GUARDAMOS EN EL LA QUERY
		$fichero_tmp=fopen("/tmp/".$nombre_fichero_tmp,"a");
		fwrite($fichero_tmp,$query);
		fclose($fichero_tmp);
			### LANZAMOS ISQL Y BUSCAMOS EL VALOR DE LA PRIMERA CELDA EN LA PRIMERA FILA
		$resultado = shell_exec("isql -b -q ".$dsn." < /tmp/".$nombre_fichero_tmp."| sed -n 4p");
		for ($i = 2; $resultado[$i] != " "; $i++) {	$valor .= $resultado[$i]; }
			### ELIMINAMOS EL TMP CREADO
		shell_exec("rm -rf /tmp/".$nombre_fichero_tmp);
			### BUSCAMOS W o C
#		if (($valor > $warning) and ($warning > 0)) { $estado = "WARNING"; $salida = 1; }
#		if (($valor > $critical) and ($critical > 0)) { $estado = "CRITICAL"; $salida = 1; }
		
		$value_out = $valor*1;
		
		if ($warning != "") { if ($value_out >= $warning) { $salida = 1; $string_salida = "WARNING - "; } }
		if ($critical != "") { if ($value_out >= $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
		if ($warning_low != "") { if ($value_out <= $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
		if ($critical_low != "") { if ($value_out <= $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
	
		$mensaje_salida = "El valor devuelto es : ".$value_out." | valor=".$value_out.";".$warning.";".$critical;
	}
}

echo $string_salida.$mensaje_salida; 

exit($salida);

?>
