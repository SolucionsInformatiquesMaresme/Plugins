#!/usr/bin/php
<?php

$host = "";
$nfs = "nagios";
$remote = "";
$help = false;
$version = false;
$salida = 0;

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "n=") !== false) { $nfs = str_replace("n=","",$arg); }
	if (strpos($arg, "r=") !== false) { $remote = str_replace("r=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_share_nfs.php Version 0.1.0\n";
	echo "check_share_nfs.php -h for help.\n";
}

if ($help == true) {
	echo "check_share_nfs.php Version 0.1.0\n\n";
	echo "\t# check_share_nfs.php H=host n=nfs r=host_remote\n";
	echo "List mode options and suboptions:\n";
	echo "H=<ip or dns name> -> Host with shares.\n";
	echo "n=<nfs> -> nfs name. With the \"n=\" option without the \"r=\" option, we will get OK if the share exists (CRITICAL, if the share is not available).\n";
	echo "n=<server ip or dns name> -> The \"r=\" option should be used with \"n=\". r= it used to identify a server is connected to an NFS share. Obtain OK if the share is available and server connectado him and CRITICAL if the share is not available or server is connected.";

}


if (($version == false) and ($help == false) and ($host != "")) {
	$remote_disponible = 0;
	$command = "";
	$executable = "showmount -e ".$host." | grep -e '".$nfs." '";
	$command = shell_exec($executable);
	if ($command == "") { 
		$nfs_disponible = 0;
		$salida = 2;
	} else {
		$nfs_disponible = 1;
		$salida = 0;
	}
	if (($remote != "") and ($nfs_disponible = 1)) {
		if (strpos($command,$remote) !== FALSE) { 
			$remote_disponible = 1;
		} else {
			$remote_disponible = 0;
			$salida = 2;
		}
	}
	if ($salida == 0) { $salida_txt = "OK - "; }
	if ($salida == 2) { $salida_txt = "CRITICAL - "; }
	if ($nfs_disponible == 1) { $salida_txt .= "NFS share <".$nfs."> is READY"; }
	if ($nfs_disponible == 0) { $salida_txt .= "NFS share <".$nfs."> is DOWN"; }
	if (($remote != "") and ($remote_disponible == 1)) { $salida_txt .= " and SERVER <".$remote."> is CONNECTED"; }
	if (($remote != "") and ($remote_disponible == 0)) { $salida_txt .= " and SERVER <".$remote."> is DISCONNECTED"; }
	echo $salida_txt." | status=".$salida."\n";
}


exit($salida)


?>