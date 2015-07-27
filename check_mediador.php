#!/usr/bin/php
<?php

# constants que s'utilitzaran durant l'execució del plugin
$host = "";
$mode = "";
$option = "";
$help = false;
$version = false;

# valors predeterminats dels W i C. Son string amb valor "" per que al final es fa una comparació per marcar W o C en funció de si s'ha insertat valor int o no.
$warning = "";
$critical = "";
$warning_low = "";
$critical_low = "";

# valors predeterminats a la sortida del plugin
$valor_final = "";
$value_out = 0;
$pnp_out = "";
$salida = 0;
$string_salida = "OK - ";


# Recollida de variables del plugin. Sempre han de ser de tipus "X=xx", i es substueix la "X=" per "".
foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "u=") !== false) { $user = str_replace("u=","",$arg); }
	if (strpos($arg, "p=") !== false) { $pass = str_replace("p=","",$arg); }

	if (strpos($arg, "d=") !== false) { $database = str_replace("d=","",$arg); }

	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }

#Aquestes variables d'entrada son del tipux -X. Si s'exejuten, no es llença res del del plugin.
	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

# Mostra la versió
if ($version == true) {
	echo "check_linux.php Version 0.1.0\n";
	echo "check_linux.php -h for help.\n";
}

# Mostra l'ajuda. tabular amb \t i salt de línia amb \n".
if ($help == true) {
	echo "check_linux.php Version 0.1.0\n\n";
	echo "Requirements: sshpass\n\n";
	echo "With SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Without SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') p=password m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Localhost:\n";
	echo "\t# check_linux.php H=127.0.0.1 m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
}


# Aquí s'executa l'script

if (($version == false) and ($help == false)) {
	# inici script

	if (($host == "") OR ($pass == "") OR ($user == "") OR ($database =="")) {
		$salida_mensaje = "Not all parameters were introduced.";
		$salida = 1;
	} else {

		$connexio = sybase_connect($host, $user, $pass) or die ("CRITICAL - La connexo no s'ha establert, NO s'han enviat traps >> 0 | Traps_enviats=0");
#		echo "Connexio establerta satisfactoriament";
		
		$db = sybase_select_db($database, $connexio);
		if (!$db){ echo "No s'ha pogut seleccionar la base de dades"; }

		sybase_close($connexio);
	}

	# final script
	# Sortida per pantalla
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

# exit per passar el valor a nagios
exit($salida);


?>