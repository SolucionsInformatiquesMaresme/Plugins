#!/usr/bin/php
<?php

$host = "";
$nfs = "nagios";
$remote = "";
$help = false;
$version = false;
$salida = 0;
$mode = "";
$anonymous = "y";
$remote_command = "";


foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "s=") !== false) { $smb = str_replace("s=","",$arg); }

	if (strpos($arg, "m=") !== false) { $mode = str_replace("m=","",$arg); }
	if (strpos($arg, "a=") !== false) { $anonymous = str_replace("a=","",$arg); }
	if (strpos($arg, "r=") !== false) { $remote_command = str_replace("r=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($version == true) {
	echo "check_share_smb.php Version 0.1.0\n";
	echo "check_share_smb.php -h for help.\n";
}

if ($help == true) {
	echo "check_share_smb.php Version 0.1.0\n\n";
	echo "\t# check_share_smb.php H=host n=smb\n";
	echo "List mode options and suboptions:\n";
	echo "H=<ip or dns name> -> Host with shares.\n";
	echo "s=<smb> -> smb name. We will get OK if the share exists (CRITICAL, if the share is not available).\n";

}


if (($version == false) and ($help == false)) {
	if ($mode == "list") {
		$remote_disponible = 0;
		$command = 0;
		$executable = "smbclient -L ".$host." -N | grep -e '".$smb." ' | wc -l";
		$command = shell_exec($executable);
		if ($command == 0) { 
			$smb_disponible = 0;
			$salida = 2;
		} else {
			$smb_disponible = 1;
			$salida = 0;
		}
		if ($salida == 0) { $salida_txt = "OK - "; }
		if ($salida == 2) { $salida_txt = "CRITICAL - "; }
		if ($smb_disponible == 1) { $salida_txt .= "SMB share <".$smb."> is READY"; }
		if ($smb_disponible == 0) { $salida_txt .= "SMB share <".$smb."> is DOWN"; }
		echo $salida_txt." | status=".$salida."\n";
	}
	
	if ($mode == "command") {
		$remote_disponible = 0;
		$command = 0;
		if ($anonymous == "y") { $user = "-N"; }
		if ($remote_command != "") { $remote_command = "-c '".$remote_command."'"; }
		
		$executable = "smbclient ".$smb." ".$user." ".$remote_command." | wc -l";
		$executable = str_replace("\\","\\\\",$executable);
#		echo $executable."\n";
		$command = shell_exec($executable);
		if ($command <= 2) { 
			$smb_disponible = 0;
			$salida = 2;
		} else {
			$smb_disponible = 1;
			$salida = 0;
		}
		if ($salida == 0) { $salida_txt = "OK - "; }
		if ($salida == 2) { $salida_txt = "CRITICAL - "; }
		if ($smb_disponible == 1) { $salida_txt .= "SMB share <".$smb."> is READY"; }
		if ($smb_disponible == 0) { $salida_txt .= "SMB share <".$smb."> is DOWN"; }
		echo $salida_txt." | status=".$salida."\n";
	}

}


exit($salida)


?>