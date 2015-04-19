#!/usr/bin/php
<?php
$user = "";
$pass = "";
$mode = "";
$domain = "";
$option = "";

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

	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }
}


if ($mode == "services_running") {
	$wmi_query = "Select displayname,name,startmode,state from win32_service";
#	$command = "wmic -U ".$user."%".$pass." //".$host." \"".$wmi_query."\" | sed '1,2 d' | sed ':a;N;$!ba;s/\\n/#/g'";
	$command = "wmic -U ".$domain."/".$user."%".$pass." //".$host." \"".$wmi_query."\"";
	$servicios_detalle = shell_exec($command);

	$service_displayname = "";
	$service_startmode = "";
	$service_state = "";
	$service_name = "";
	$variable = 1;
	$service_exception = 0;
	$service_auto = 0;
	$service_auto_running = 0;
	$service_auto_running_no = "";
	for ($i = 1; $i < strlen($servicios_detalle); $i++) {
		if ($servicios_detalle[$i] == "\n") {
			if ((strpos($option,$service_displayname)) !== FALSE) { 
				$service_exception++;
			} else {
				if ($service_startmode == "Auto") { $service_auto++; }
				if (($service_startmode == "Auto") AND ($service_state == "Running")) { $service_auto_running++; }
				if (($service_startmode == "Auto") AND ($service_state != "Running")) { $service_auto_running_no .= " >>".$service_displayname; }
			}
#			echo $service_displayname." ".$service_startmode." ".$service_state."\n";
			$service_displayname = "";
			$service_name = "";
			$service_startmode = "";
			$service_state = "";
			$variable = 1;
		} else {
			if ($servicios_detalle[$i] == "|") { $variable++; }
			if (($servicios_detalle[$i] != "|") AND ($variable == 1)) { $service_displayname .= $servicios_detalle[$i]; }
			if (($servicios_detalle[$i] != "|") AND ($variable == 2)) { $service_name .= $servicios_detalle[$i]; }
			if (($servicios_detalle[$i] != "|") AND ($variable == 3)) { $service_startmode .= $servicios_detalle[$i]; }
			if (($servicios_detalle[$i] != "|") AND ($variable == 4)) { $service_state .= $servicios_detalle[$i]; }
		}
		
	}
	$value_out = $service_auto-$service_auto_running;
	$string_out = "Service 'Auto' and 'Running' ".$service_auto_running."/".$service_auto." and ".$service_exception." exceptions (".$value_out." service 'Auto' not 'Running'".$service_auto_running_no.")";
	$string_out = str_replace("\n","",$string_out);
	$pnp_out = "auto_no_running=".$value_out.";".$warning.";".$critical;
}


if ($warning != "") { if ($value_out > $warning) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical != "") { if ($value_out > $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
if ($warning_low != "") { if ($value_out < $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical_low != "") { if ($value_out < $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
echo $string_salida.$string_out." | ".$pnp_out."\n";
exit($salida)


?>

