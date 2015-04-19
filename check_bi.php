#!/usr/bin/php
<?php

$help = 0;
$salida=0;
$warning = "";
$critical = "";

$status = "";
$id = "";

$estado = "OK";
$mensaje_salida = "";
$valor = "";


foreach ($argv as $arg) {
        if (strpos($arg, "-h") !== false) { $help=1; }
        if (strpos($arg, "s=") !== false) { $status = str_replace("s=","",$arg); }
        if (strpos($arg, "i=") !== false) { $id = str_replace("i=","",$arg); }
        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

if ($help == 1) {
	$help_text = "Hay que identificar tres partes:\n";
	$help_text .= "\n";
	$help_text .= "i=identificadores\n";
	$help_text .= "Los identificadores son los servicios que vamos a querer revisar. Se identifican con tres campos separados por el carácter \",\" (coma). Para separar los servicios se utiliza el carácter \";\" (punto y coma). Los campos a identificar son: nombre del host, nombre del servicio e identificador de nivel de alarma.\n";
	$help_text .= "\n";
	$help_text .= "s=identificador de nivel de alarma\n";
	$help_text .= "Los servicios pueden tener hasta 4 estados (0=ok, 1=alarma, 2=crítico, 3=desconocido). Estos 4 estados se deben informar con el identificador, separados los campos por el carácter \",\". Pueden añadirse tantos identificadores de nivel de alarma como sean necesarios, todos ellos separados por el carácter \";\".\n";
	$help_text .= "\n";
	$help_text .= "w=warning\n";
	$help_text .= "Salida que esperamos encontrar en caso de \"warning\". Puede ponerse más de una opción, siempre separado por el carácter \",\".\n";
	$help_text .= "\n";
	$help_text .= "c=critical\n";
	$help_text .= "Salida que esperamos encontrar en caso de \"critical\". Puede ponerse más de una opción, siempre separado por el carácter \",\".\n";
	$help_text .= "\n";
	$help_text .= "EJEMPLO:\n";
	$help_text .= "check_bi i=\"host_1,servicio_1,1;host_2,servicio_2,2;host_1;servicio_3,2\" s=\"1,1,0,0,0;2;0,0,1,1\" w=\"110,111\" c=\"011\"\n";
	echo $help_text;
}



$file="/opt/monitor/var/status.log";
$file_temp = "/tmp/check_bi".rand(1000000,9999999).".dat";
$command = "cat ".$file." | grep -e servicestatus -e hoststatus -e servicecomment -e host_name -e service_description -e current_state -e last_update";
$command .= "| grep -v long_plugin_output | sed 's/\t//g' | sed 's/hoststatus {/hoststatus===LLLINEEEhoststatus/g' | sed 's/servicecomment {/servicecomment===LLLINEEEservicecomment/g' | sed 's/servicestatus {/servicestatus===LLLINEEEservicestatus/g' |";
$command .= " sed 's/host_name=/host_name===CCCAMPPP/g' | sed 's/hoststatus {/hoststatus===CCCAMPPP/g' | sed 's/service_description=/service_description===CCCAMPPP/g' | sed 's/current_state=/current_state===CCCAMPPP/g'";
$command .= " | sed 's/plugin_output=/plugin_output===CCCAMPPP/g' | sed 's/last_update=/last_update===CCCAMPPP/g' | awk -F'===' '{print $2}' | sed -n -e '1x;1!H;"."$"."{x;s-\\n--gp}'";
$command .= "| sed 's/CCCAMPPP/;/g' | sed 's/LLLINEEE/\\n/g' | grep -v 'Nagios Remotes' > ".$file_temp;
shell_exec($command);


#s=1,1,0,0,0;
#i=host_1,service1,1;host_2,service_2,1;host_3,service_3
#i=xendes03.bcgest.es,Network,1;webservices.bcgest.es,PING,1;rigodon.bcgest.es,SWAP,1;
#c=000,011,101,110,111

$host = "";
$service = "";
$service_status = "";

$estado_final = "";
$variable = 1;
for ($i = 0; $i < strlen($id); $i++) {
	if ($id[$i] == ",") { $variable++; }
	if ($id[$i] == ";") { 
		$service_value = shell_exec($command_service = "cat ".$file_temp." | grep -e ';".$host.";".$service."'");

		#### BUSCAMOS EL ESTADO DEL SERVICIO 0,1,2,3
		$estado_actual = "";
		$variable = 1;
		for ($x = 0; $x < strlen($service_value); $x++) {
			if ($service_value[$x] == ";") { $variable++; }
			if (($service_value[$x] != ";") AND ($variable == 4)) { $estado_actual .= $service_value[$x]; }
		}
#		echo $host." ".$service." ".$service_status." ".$estado_actual."\n";

		#### COMPARAMOS EL ESTADO DEL SERVICIO CON EL VALOR QUE QUEREMOS DEL STRING NUMERO X
		$variable = 1;
		$id_estado = "";
		$ok = "";
		$warning_state = "";
		$critical_state = "";
		$unknown = "";

		for ($z = 0; $z < strlen($status); $z++) {
			if ($status[$z] == ",") { $variable++; }
			if ($status[$z] == ";") { 
				if ($service_status == $id_estado) {
#					echo "id=".$id_estado." ok=".$ok." warning_state=".$warning_state." critical_state=".$critical_state." unknown=".$unknown."\n";
					if ($estado_actual == "0") { $estado_final .= $ok; }
					if ($estado_actual == "1") { $estado_final .= $warning_state; }
					if ($estado_actual == "2") { $estado_final .= $critical_state; }
					if ($estado_actual == "3") { $estado_final .= $unknown; }
				}
				$variable = 1;
				$id_estado = "";
				$ok = "";
				$warning_state = "";
				$critical_state = "";
				$unknown = "";
			}
			if (($status[$z] != ",") AND ($status[$z] != ";")){ 
				if ($variable == 1) { $id_estado .= $status[$z]; }
				if ($variable == 2) { $ok .= $status[$z]; }
				if ($variable == 3) { $warning_state .= $status[$z]; }
				if ($variable == 4) { $critical_state .= $status[$z]; }
				if ($variable == 5) { $unknown .= $status[$z]; }
			}
			
		}
		$variable = 1;
		$host = "";
		$service = "";
		$service_status = "";
	}
	if (($id[$i] != ",") AND ($id[$i] != ";")){ 
		if ($variable == 1) { $host .= $id[$i]; }
		if ($variable == 2) { $service .= $id[$i]; }
		if ($variable == 3) { $service_status .= $id[$i]; }
	}
}
shell_exec("rm -rf ".$file_temp);

#echo $estado_final."\n";

$salida = 0;
$string_salida = "OK - all conditions succes: status ".$estado_final;
$pnp_out = "out=0";

if ($warning != "")  { if ((strpos($warning,$estado_final)) !== FALSE)  { $salida = 1; $string_salida = "WARNING - status ".$estado_final; $pnp_out = "out=1"; } }
if ($critical != "") { if ((strpos($critical,$estado_final)) !== FALSE) { $salida = 1; $string_salida = "CRITICAL - status ".$estado_final; $pnp_out = "out=2";} }



echo $string_salida." | ".$pnp_out."\n";

exit($salida);

?>
