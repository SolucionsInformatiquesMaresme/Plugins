#!/usr/bin/php
<?php

$host = "";
$user = "nagios";
$pass = "";
$cert = "";
$mode = "";
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

	if (strpos($arg, "m=") !== false) { $mode = str_replace("m=","",$arg); }
	if (strpos($arg, "o=") !== false) { $option = str_replace("o=","",$arg); }
	if (strpos($arg, "s=") !== false) { $suboption = str_replace("s=","",$arg); }
	if (strpos($arg, "3=") !== false) { $option3 = str_replace("3=","",$arg); }

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
	echo "check_unix.php Version 0.1.0\n";
	echo "check_unix.php -h for help.\n";
}

if ($help == true) {
	echo "check_unix.php Version 0.1.0\n\n";
	echo "Requirements: sshpass\n\n";
	echo "With SSL Certificate installed:\n";
	echo "\t# check_unix.php H=host u=user(default 'nagios') m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Without SSL Certificate installed:\n";
	echo "\t# check_unix.php H=host u=user(default 'nagios') p=password m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Localhost:\n";
	echo "\t# check_unix.php H=127.0.0.1 m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "List mode options and suboptions:\n";
	echo "m=filesystem -> Filesystem space used in %.\n";
	echo "\to=filesystem_name -> Filesystem name, example o=/boot\n";
	echo "m=filesystem_inode -> Filesystem space used in %.\n";
	echo "\to=filesystem_name -> Filesystem name, example o=/boot\n";
	echo "m=memory -> Memory used in %.\n";
	echo "m=swap -> Swap used in %.\n";
	echo "m=cpu -> CPU used in %.\n";


}


if (($version == false) and ($help == false) and ($mode != "")) {
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

	if (($mode == "filesystem") or ($mode == "filesystem_inode")){
		if ($mode == "filesystem") { $command = "bdf | grep '".$option."$'"; }
		if ($mode == "filesystem_inode") { $command = "bdf -i | grep '".$option."$'"; }
		$value = str_replace("%","",command($command,$connect,$host));
		$variable = 0;
		$total_space = "";
		$total_used = "";
		$total_free = "";
		$total_percent = "";
		for ($i = 1; $i < strlen($value); $i++) {
			if (($value[$i] != " ") AND ($value[$i-1] == " ")) { $variable++; }
			if (($value[$i] != " ") AND ($variable == 1)) { $total_space .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 2)) { $total_used .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $total_free .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 4)) { $total_percent .= $value[$i]; }
		}
		$total_space = round(($total_space/1024),2);
		$total_used = round(($total_used/1024),2);
		$total_free = round(($total_free/1024),2);
		$total_percent = $total_percent*1;
		$string_out = "Space ".$total_space."Mb total, ".$total_used."Mb used, ".$total_free."Mb free, <".$option."> % used >> ".$total_percent;
		$value_out = $total_percent;
		$pnp_out = "%=".$value_out.";".$warning.";".$critical." Total_Mb=".$total_space." Used_Mb=".$total_used." Free Mb=".$total_free."";
	}

	if ($mode == "memory") {
		$command = "vmstat | tail -n 1";
		$value = command($command,$connect,$host);
		$value = str_replace("\n","",$value);
		$procs_r = "";
		$procs_b = "";
		$procs_w = "";
		$avm = "";
		$free = "";
		$re = "";
		$at = "";
		$pi = "";
		$po = "";
		$fr = "";
		$de = "";
		$sr = "";
		$in = "";
		$sy = "";
		$cs = "";
		$cpu_us = "";
		$cpu_sy = "";
		$cpu_id = "";
		$variable = 0;
		for ($i = 0; $i < strlen($value); $i++) {
			if (($value[$i] != " ") AND ($value[$i-1] == " ")) { $variable++; }
			if (($value[$i] != " ") AND ($variable == 1)) { $procs_r .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 2)) { $procs_b .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $procs_w .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 4)) { $avm .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 5)) { $free .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 6)) { $re .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 7)) { $at .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 8)) { $pi .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 9)) { $po .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 10)) { $fr .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 11)) { $de .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 12)) { $sr .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 13)) { $in .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 14)) { $sy .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 15)) { $cs .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 16)) { $cpu_us .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 17)) { $cpu_sy .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 18)) { $cpu_id .= $value[$i]; }
		}
		$total_total = round(($avm/1024),2);
		$total_used = round((($avm-$free)/1024),2);
		$total_free = round(($free/1024),2);
		$total_percent = round($total_used/($total_total/100),2);
		$string_out = "Memory ".$total_total."Mb total, ".$total_used."Mb used, ".$total_free."Mb free, % used >> ".$total_percent;
		$value_out = $total_percent;
		$pnp_out = "%=".$value_out.";".$warning.";".$critical;
	}


	if ($mode == "swap") {
		$command = "swapinfo -tam | grep total";
		$value = command($command,$connect,$host);
		$value = str_replace("\n","",$value);
		$description = "";
		$avail = "";
		$used = "";
		$free = "";
		$used_perc = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value); $i++) {
			if (($value[$i] != " ") AND ($value[$i-1] == " ")) { $variable++; }
			if (($value[$i] != " ") AND ($variable == 1)) { $description .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 2)) { $avail .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $used .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 4)) { $free .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 5)) { $used_perc .= $value[$i]; }
		}
		$total_total = round(($avail*1),2);
		$total_used = round(($used*1),2);
		$total_free = round(($free*1),2);
		$total_percent = round($total_used/($total_total/100),2);
		$string_out = "Swap ".$total_total."Mb total, ".$total_used."Mb used, ".$total_free."Mb free, % used >> ".$total_percent;
		$value_out = $total_percent;
		$pnp_out = "%=".$value_out.";".$warning.";".$critical;
	}


	if ($mode == "cpu") {
		$command = "sar -u 1 5 | grep Average";
		$value = command($command,$connect,$host);
		$value = str_replace("\n","",$value);
		$value_cpu = "";
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == " ") { $value_cpu = ""; }
			else { $value_cpu .= $value[$i]; }
		}
		$cpu = 100-$value_cpu;
		$string_out = "CPU % usage >> ".$cpu;
		$value_out = $cpu;
		$pnp_out = "%=".$cpu;
}



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


exit($salida)


?>