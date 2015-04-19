#!/usr/bin/php
<?php

$host = "";
$user = "nagios";
$pass = "";
$cert = "";
$file = "";
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
        if (strpos($arg, "c=") !== false) { $cert = str_replace("c=","",$arg); }

        if (strpos($arg, "f=") !== false) { $file = str_replace("f=","",$arg); }

        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
        if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
        if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }

        if (strpos($arg, "-h") !== false) { $help = true; }
        if (strpos($arg, "-v") !== false) { $version = true; }

}

$host_local = 0;
if (($host == "127.0.0.1") or ($host == "localhost") or ($host == "")) { $host_local = 1; }

if ($version == true) {
	echo "check_selenium.php Version 0.0.1\n";
	echo "check_selenium.php -h for help.\n";
}

if ($help == true) {
	echo "Requirements: sshpass\n\n";
	echo "With SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Without SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') p=password m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Localhost:\n";
	echo "\t# check_linux.php H=127.0.0.1 m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "List mode options and suboptions:\n";
	echo "f=file -> Path and file complet.\n";
}


if (($version == false) and ($help == false)) {
	if ($pass == "") {	
		$connect = "ssh ".$user."@".$host;
	} else {
		$connect = "sshpass -p '".$pass."' ssh ".$user."@".$host;
	}

	function command($command,$connect,$host) {
		$result = "";
		global $host_local;
		if ($host_local == 1) {
			$result = shell_exec($command);
		} else {
			$result = shell_exec($connect." '".$command."'");
		}
		if ($result == "") {
			echo "UNKNOWN - Connection is not possible or command no exist on ".$host." host.";
			$salida = 3;
			exit($salida);
		}
		return $result;
	}

	if ($file != "") {
		$command = "ls -lt ".$file."* | sed -n \"1 p\" | awk \"{print \\$9}\"";
		$value = str_replace("\n","",command($command,$connect,$host));
		$command = "cat ".$value;
		$content_file = command($command,$connect,$host);
		if ((strpos($content_file,"errors=")) !== FALSE) {
			$posicion = (strpos($content_file,"errors="))+7;
			$variable = 0;
			$errors = "";
			for ($i = $posicion; $i < strlen($content_file); $i++) {
				if ($content_file[$i] == "\"") { $variable++; }
				if (($content_file[$i] != "\"") AND ($variable == 1)) { $errors .= $content_file[$i]; }
			}
			$errors = $errors *1;
		}

		if ((strpos($content_file,"time=")) !== FALSE) {
			$posicion = (strpos($content_file,"time="))+5;
			$variable = 0;
			$time = "";
			for ($i = $posicion; $i < strlen($content_file); $i++) {
				if ($content_file[$i] == "\"") { $variable++; }
				if (($content_file[$i] != "\"") AND ($variable == 1)) { $time .= $content_file[$i]; }
			}
			$time = $time *1;
		}

		if ((strpos($content_file,"name=")) !== FALSE) {
			$posicion = (strpos($content_file,"name="))+5;
			$variable = 0;
			$name = "";
			for ($i = $posicion; $i < strlen($content_file); $i++) {
				if ($content_file[$i] == "\"") { $variable++; }
				if (($content_file[$i] != "\"") AND ($variable == 1)) { $name .= $content_file[$i]; }
			}
		}

		if ((strpos($content_file,"error message=")) !== FALSE) {
			$posicion = (strpos($content_file,"error message="))+14;
			$variable = 0;
			$error = "";
			for ($i = $posicion; $i < strlen($content_file); $i++) {
				if ($content_file[$i] == "\"") { $variable++; }
				if (($content_file[$i] != "\"") AND ($variable == 1)) { $error .= $content_file[$i]; }
			}
		}
		if ($error == "") { 
			$error_description = "";
		} else {
			$error_description = " Error (".$error.")";
		}

		if ($errors > 0) {
			echo "CRITICAL - Selenium check ".$value." in ".$time." seconds and ".$errors." errors.".$error_description." | errors=".$errors.";; time=".$time.";;";
			$salida = 2;
		} else {
			echo "OK - Selenium check ".$value." in ".$time." seconds and ".$errors." errors.".$error_description." | errors=".$errors.";; time=".$time.";;";
		}
#		echo $value;
		echo "\n";
#		$in = round(((($in_2-$in_1)/10)/1024),0);
#		$out = round(((($out_2-$out_1)/10)/1024),0);
#		$value_out = round(($in+$out),0);
#		$string_out = "Bandwidth ".$option." IN=".$in."Kb/sec OUT=".$out."Kb/sec, Total bandwith in Kb/sec >> ".$value_out;
#		$pnp_out = "Total_Kb_sec=".$value_out.";".$warning.";".$critical;
	}

#	if ($warning != "") { if ($value_out >= $warning) { $salida = 1; $string_salida = "WARNING - "; } }
#	if ($critical != "") { if ($value_out >= $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
#	if ($warning_low != "") { if ($value_out <= $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
#	if ($critical_low != "") { if ($value_out <= $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
#	if ($pnp_out == "") {
#		echo $string_salida.$string_out."\n";
#	} else {
#		echo $string_salida.$string_out." | ".$pnp_out."\n";
#	}

}


exit($salida)


?>