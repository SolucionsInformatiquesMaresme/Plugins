#!/usr/bin/php
<?php

$help = 0;
$salida=0;
$warning = "";
$critical = "";
$servicios_no_encontrados = 0;

$status = "";
$id = "";

$estado = "OK";
$salida_detallada = "";
$mensaje_salida = "";
$valor = "";


foreach ($argv as $arg) {
        if (strpos($arg, "-h") !== false) { $help=1; }
        if (strpos($arg, "s=") !== false) { $services = str_replace("s=","",$arg); }
        if (strpos($arg, "f=") !== false) { $file_logica = str_replace("f=","",$arg); }
}

if ($help == 1) {
	$help_text = "Hay que identificar tres partes:\n";
}
########### CREEM EL FITXER ESTAT
$file="/usr/local/nagios/var/status.dat";
$file_temp = "/tmp/check_bi_pro_".rand(1000000,9999999).".dat";
$command = "cat ".$file." | grep -e servicestatus -e hoststatus -e servicecomment -e host_name -e service_description -e current_state -e last_update";
$command .= "| grep -v long_plugin_output | sed 's/\t//g' | sed 's/hoststatus {/hoststatus===LLLINEEEhoststatus/g' | sed 's/servicecomment {/servicecomment===LLLINEEEservicecomment/g' | sed 's/servicestatus {/servicestatus===LLLINEEEservicestatus/g' |";
$command .= " sed 's/host_name=/host_name===CCCAMPPP/g' | sed 's/hoststatus {/hoststatus===CCCAMPPP/g' | sed 's/service_description=/service_description===CCCAMPPP/g' | sed 's/current_state=/current_state===CCCAMPPP/g'";
$command .= " | sed 's/plugin_output=/plugin_output===CCCAMPPP/g' | sed 's/last_update=/last_update===CCCAMPPP/g' | awk -F'===' '{print $2}' | sed -n -e '1x;1!H;"."$"."{x;s-\\n--gp}'";
$command .= "| sed 's/CCCAMPPP/;/g' | sed 's/LLLINEEE/\\n/g' | grep -v 'Nagios Remotes' > ".$file_temp;
#echo $command."\n";

shell_exec($command);

########### POSSEM TOTS ELS SERVEIS DINTRE DE VARIABLES
$service = array();
$valor = "";
$variable=1;
for ($i = 0; $i < strlen($services); $i++) {
	if ($services[$i] == ">") {
		$service_value = shell_exec("cat ".$file_temp." | grep -e ';".str_replace(",",";",str_replace("]","\]",str_replace("[","\[",$valor)))."'");

		$estado_actual = "";
		$variable_service = 1;
		for ($x = 0; $x < strlen($service_value); $x++) {
			if ($service_value[$x] == ";") { $variable_service++; }
			if (($service_value[$x] != ";") AND ($variable_service == 4)) { $estado_actual .= $service_value[$x]; }
		}
		if (strlen($service_value) == 0) {
			$estado = "NO EXIST";
			$servicios_no_encontrados = 1;
		} else {
			if ($estado_actual == 0) { $estado = "OK"; }
			if ($estado_actual == 1) { $estado = "WARNING"; }
			if ($estado_actual == 2) { $estado = "CRITICAL"; }
			if ($estado_actual == 3) { $estado = "UNKNOWN"; }
		}
		$salida_detallada .= $valor."=".$estado."\n";
		$service[$variable] = $estado_actual;
		$variable++;
		$valor="";
	}
	if ($services[$i] != ">") { $valor .= $services[$i]; }
}
#var_dump($service);


$valores = array();

$file = fopen($file_logica, "r") or exit("Unable to open file!");
$cadena = "";
$operacion_actual = 1;
while(!feof($file)) {
	$operacion = "";
	$servicio = "";
	$valor1 = "";
	$valor2 = "";
	$valor3 = "";
	$valor4 = "";
	
	#### MIRAMOS CADA FILA DEL FICHERO
	$fila = str_replace("\n","",fgets($file));
	$variable = 1;
	for ($i = 0; $i < strlen($fila); $i++) {
		if ($fila[$i] == ",") { $variable++; }
		if (($fila[$i] != ",") AND ($variable == 1)) { $operacion .= $fila[$i]; }
		if (($fila[$i] != ",") AND ($variable == 2)) { $servicio .= $fila[$i]; }
		if (($fila[$i] != ",") AND ($variable == 3)) { $valor1 .= $fila[$i]; }
		if (($fila[$i] != ",") AND ($variable == 4)) { $valor2 .= $fila[$i]; }
		if (($fila[$i] != ",") AND ($variable == 5)) { $valor3 .= $fila[$i]; }
		if (($fila[$i] != ",") AND ($variable == 6)) { $valor4 .= $fila[$i]; }
	}
	if ($operacion != $operacion_actual) {
		$cadena_anterior = $cadena;
		$operacion_actual++;
		$cadena = "";
	}

	if ($operacion != "") {
		if (($servicio == "w") or ($servicio == "c")) {
			if ($servicio == "w") {	if (strpos($valor1,$cadena) !== FALSE) { $valores[$operacion] = 1; } else { $valores[$operacion] = 0; }}
			if ($servicio == "c") {	if (strpos($valor1,$cadena) !== FALSE) { $valores[$operacion] = 2; }}
		} else {
			if (strpos($servicio,"*") !== FALSE) {
#				str_replace("*","",$servicio);
				$valor1 = $valor1*1;
				$valor2 = $valor2*1;
				$valor3 = $valor3*1;
				$valor4 = $valor4*1;
				if ($valores[str_replace("*","",$servicio)] == 0) { $cadena .= $valor1;}
				if ($valores[str_replace("*","",$servicio)] == 1) { $cadena .= $valor2;}
				if ($valores[str_replace("*","",$servicio)] == 2) { $cadena .= $valor3;}
				if ($valores[str_replace("*","",$servicio)] == 3) { $cadena .= $valor4;}

			} else {
				$valor1 = $valor1*1;
				$valor2 = $valor2*1;
				$valor3 = $valor3*1;
				$valor4 = $valor4*1;
				if ($service[$servicio] == 0) { $cadena .= $valor1;}
				if ($service[$servicio] == 1) { $cadena .= $valor2;}
				if ($service[$servicio] == 2) { $cadena .= $valor3;}
				if ($service[$servicio] == 3) { $cadena .= $valor4;}
			}
		}
#		echo $cadena."|".$operacion_actual."||".$operacion."|".$servicio."|".$valor1."|".$valor2."|".$valor3."|".$valor4."\n";
	}
}
fclose($file);

$salida = $valores[($operacion_actual-1)];
$string_salida = "OK - Returned value=".$cadena_anterior." | value=".$salida;
if ($salida == 1) { $string_salida = "WARNING - Returned value=".$cadena_anterior." | value=".$salida; }
if ($salida == 2) { $string_salida = "CRITICAL - Returned value=".$cadena_anterior." | value=".$salida; }
if ($servicios_no_encontrados == 1) { $salida = 3; $string_salida = "UNKNOWN - Not all services exist | value=".$salida; }
echo $string_salida."\n".$salida_detallada;
shell_exec("rm -rf ".$file_temp);
exit($salida);

?>
