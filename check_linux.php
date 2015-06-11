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
	echo "check_linux.php Version 0.1.0\n";
	echo "check_linux.php -h for help.\n";
}

if ($help == true) {
	echo "check_linux.php Version 0.1.0\n\n";
	echo "Requirements: sshpass\n\n";
	echo "With SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Without SSL Certificate installed:\n";
	echo "\t# check_linux.php H=host u=user(default 'nagios') p=password m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "Localhost:\n";
	echo "\t# check_linux.php H=127.0.0.1 m=mode o=option s=suboption w=warning(up) W=warning(down) c=critial(up) C=critical(down)\n";
	echo "List mode options and suboptions:\n";
	echo "m=uptime -> Time in seconds that the host takes on.\n";
	echo "m=version -> SO version (in /etc/issue).\n";
	echo "m=filesystem -> Filesystem space used in %.\n";
	echo "\to=filesystem_name -> Filesystem name, example o=/boot\n";
	echo "m=filesystem_inode -> Filesystem space used in %.\n";
	echo "\to=filesystem_name -> Filesystem name, example o=/boot\n";
	echo "m=memory -> Memory used in %.\n";
	echo "m=swap -> Swap used in %.\n";
	echo "m=memory_buffers_cached -> Memory used without buffers and cached memory, in %.\n";
	echo "m=cpuload -> CPU load last 1, 5 and 15 minuts.\n";
	echo "m=cpu -> CPU used in %.\n";
	echo "m=cpu_pro -> CPU used full details (without warnings or criticals).\n";	
	echo "m=proces -> Count proces with name in option.\n";
	echo "\to=proces_name -> Proces name, example o=crond\n";
	echo "m=users -> Count connected users.\n";
	echo "m=time -> Host time in Unix Time.\n";
	echo "m=time_vs_localhost -> Time difference between local and remote server in seconds.\n";
	echo "\n";
	echo "m=file_date -> Date last modify (warning and critical in seconds).\n";
	echo "\to=<path/file> -> Path and File name.\n";
	echo "m=interface -> Interface bandwidth (in/out 10 seconds).\n";
	echo "\to=<interface> -> Interface name (eth0, wlan0, ...).\n";
	echo "m=ping -> Ping to another host (in/out 10 seconds).\n";
	echo "\to=<ip> -> IP host (192.168.1.100, ...).\n";

	
#	echo "m=path -> Path name (need a 'option').\n";
#	echo "\to=files -> count files\n";
#	echo "\to=size -> size of all files\n";
#	echo "\t\ts=size -> include a tree of files (option 'files' and 'size')\n";
	

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

	function command_and_local_command($command,$local_command,$connect,$host) {
		$result = "";
		$result = shell_exec($connect." '".$command."' ".$local_command);
		if ($result == "") {
			echo "UNKNOWN - Connection is not possible or command no exist on ".$host." host.";
			$salida = 3;
			exit($salida);
		}
		return $result;
	}

	if ($mode == "uptime") {
		$command = "cat /proc/uptime | sed 1q";
		$value = command($command,$connect,$host);
		for ($i = 0; $i <= strlen($value); $i++) {
			if ($value[$i] == ".") { break; } else { $valor_final .= $value[$i]; }
		}
		$valor_final = intval($valor_final);
		$days = floor($valor_final/86400);
		$hours = floor(($valor_final-($days*86400))/3600);
		$minuts = floor(($valor_final-($days*86400)-($hours*3600))/60);
		$seconds = $valor_final-($days*86400)-($hours*3600)-($minuts*60);
		$string_out = "Uptime ".$days." days, ".$hours." hous, ".$minuts." minuts, ".$seconds." second >> ".$valor_final." seconds";
		$value_out = $valor_final;
		$pnp_out = "seconds=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "version") {
		$command = "cat /etc/issue | sed '/^$/d'";
		$value = command($command,$connect,$host);
		$value = str_replace(chr(92),"",$value);
		$value = str_replace("\n"," ",$value);
		$value = str_replace("|"," ",$value);
		$string_out = "OS Version >> ".$value;
	}

	if (($mode == "filesystem") or ($mode == "filesystem_inode")){
		if ($mode == "filesystem") { $command = "df | grep '".$option."$'"; }
		if ($mode == "filesystem_inode") { $command = "df -i | grep '".$option."$'"; }
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

	if ($mode == "proces") {
		$option_inicial = $option;
		$option = str_replace("[","\[",$option);
		$option = str_replace("]","\]",$option);
		$command = "ps aux";
		$local_command = "| grep -e \"".$option."\" | wc -l";
		$value = command_and_local_command($command,$local_command,$connect,$host);
		$value_out = $value*1;
		$string_out = "Number of processes ".$option_inicial." >> ".$value_out;
		$pnp_out = "proces=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "proces_total") {
		$command = "ps aux";
		if ($option == "") { 
			$local_command = "| wc -l";
		} else {
			$local_command = "| grep -e ' ".$option."' | wc -l";
		}
		$value = command_and_local_command($command,$local_command,$connect,$host);
		$value_out = $value*1;
		$string_out = "Number of processes ".$option." >> ".$value_out;
		$pnp_out = "proces=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "users") {
		$command = "w | wc -l";
		$value = command($command,$connect,$host);
		$value_out = ($value*1)-2;
		$string_out = "Number of connected users >> ".$value_out;
		$pnp_out = "users=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "time") {
		$command = "date +%s";
		$value = command($command,$connect,$host);
		$value = $value*1;
		$value_formated = shell_exec("date -d @".$value);
		$value_formated = str_replace("\n","",$value_formated);
		$value_out = $value;
		$string_out = "Server time <".$value_formated.">, unix time >> ".$value_out;
		$pnp_out = "time=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "time_vs_localhost") {
		$command = "date +%s";
		$value = command($command,$connect,$host);
		$value_localhost = shell_exec("date +%s");
		$value_out = abs($value - $value_localhost);
		$string_out = "Offset server time vs localhost in seconds >> ".$value_out;
		$pnp_out = "time=".$value_out.";".$warning.";".$critical;
	}



	if (($mode == "memory") or ($mode == "swap") or ($mode == "memory_buffers_cached")) {
		if (($mode == "memory") or ($mode == "memory_buffers_cached")) { $linea = 2; $type = "Memory"; }
		if ($mode == "swap") { $linea = 4; $type = "SWAP"; }
		$command = "free | sed -n ".$linea."p";
		$value = command($command,$connect,$host);
		$total = "";
		$used = "";
		$free = "";
		$shared = "";
		$buffers = "";
		$cached = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value); $i++) {
			if (($value[$i] != " ") AND ($value[$i-1] == " ")) { $variable++; }
			if (($value[$i] != " ") AND ($variable == 2)) { $total .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $used .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 4)) { $free .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 5)) { $shared .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 6)) { $buffers .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 7)) { $cached .= $value[$i]; }
		}
		if (($mode == "memory") or ($mode == "swap")) {
			$total_total = round(($total/1024),2);
			$total_used = round(($used/1024),2);
			$total_free = round(($free/1024),2);
			$total_percent = round($used/($total/100),2);
		}
		if ($mode == "memory_buffers_cached") {
			$total_total = round(($total/1024),2);
			$total_used = round((($total-($free+$buffers+$cached))/1024),2);
			$total_free = round((($free+$buffers+$cached)/1024),2);
			$total_percent = round(($total-($free+$buffers+$cached))/($total/100),2);
		}
		$string_out = $type." ".$total_total."Mb total, ".$total_used."Mb used, ".$total_free."Mb free, % used >> ".$total_percent;
		$value_out = $total_percent;
		$pnp_out = "%=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "cpuload") {
		$command = "cat /proc/cpuinfo | grep processor | wc -l";
		$value = command($command,$connect,$host);
		$cpus = $value*1;
		$command = "cat /proc/loadavg";
		$value = command($command,$connect,$host);
		$min01 = "";
		$min05 = "";
		$min15 = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == " ") { $variable++; }
			if (($value[$i] != " ") AND ($variable == 1)) { $min01 .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 2)) { $min05 .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $min15 .= $value[$i]; }
		}
		$min01 = round($min01*1,2);
		$min05 = $min05*1;
		$min15 = $min15*1;
		$string_out = $cpus."xCPU Load last 1minut=".$min01.", last 5minuts=".$min05.", last 15 minuts >> ".$min15;
		$value_out = $min15;
		$pnp_out = "load=".$min15.";".$warning.";".$critical;
	}

	if ($mode == "cpu") {
		$command = "cat /proc/stat | grep \"cpu \" | awk \"{print \\$5}\" && sleep 1 && cat /proc/stat | grep \"cpu \" | awk \"{print \\$5}\"";
		$value = command($command,$connect,$host);
		$value = str_replace("\n",";",$value);
		$value_1 = "";
		$value_2 = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == ";") { $variable++; }
			if (($value[$i] != ";") AND ($variable == 1)) { $value_1 .= $value[$i]; }
			if (($value[$i] != ";") AND ($variable == 2)) { $value_2 .= $value[$i]; }
		}
		$cpu = round((($value_2-$value_1)/100),2);
		$string_out = "CPU % usage >> ".$cpu;
		$value_out = $cpu;
		$pnp_out = "%=".$cpu;
	}

	if ($mode == "cpu_sar") {
		$command = "sar -u 1 5 | tail -1 | awk \"{print \\$8}\"";
		$value = command($command,$connect,$host);
		$value = str_replace("\n",";",$value);
		$cpu = 100 - $value;
		$string_out = "CPU % usage >> ".$cpu;
		$value_out = $cpu;
		$pnp_out = "%=".$cpu;
	}

	if ($mode == "cpu2") {
		#     user    nice   system  idle      iowait irq   softirq  steal  guest  guest_nice
		#cpu  74608   2520   24433   1117073   6176   4054  0        0      0      0
		$command = "cat /proc/stat | grep 'cpu '";
		$value_1 = command($command,$connect,$host);
		$user_1 = "";
		$nice_1 = "";
		$system_1 = "";
		$idle_1 = "";
		$iowait_1 = "";
		$irq_1 = "";
		$softirq_1 = "";
		$steal_1 = "";
		$guest_1 = "";
		$guest_nice_1 = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value_1); $i++) {
			if (($value_1[$i] != " ") AND ($value_1[$i-1] == " ")) { $variable++; }
			if (($value_1[$i] != " ") AND ($variable == 2)) { $user_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 3)) { $nice_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 4)) { $system_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 5)) { $idle_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 6)) { $iowait_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 7)) { $irq_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 8)) { $softirq_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 9)) { $steal_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 10)) { $guest_1 .= $value_1[$i]; }
			if (($value_1[$i] != " ") AND ($variable == 11)) { $guest_nice_1 .= $value_1[$i]; }
		}

		$command = "cat /proc/stat | grep 'cpu '";
		$value_2 = command($command,$connect,$host);
		$user_2 = "";
		$nice_2 = "";
		$system_2 = "";
		$idle_2 = "";
		$iowait_2 = "";
		$irq_2 = "";
		$softirq_2 = "";
		$steal_2 = "";
		$guest_2 = "";
		$guest_nice_2 = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value_2); $i++) {
			if (($value_2[$i] != " ") AND ($value_2[$i-1] == " ")) { $variable++; }
			if (($value_2[$i] != " ") AND ($variable == 2)) { $user_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 3)) { $nice_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 4)) { $system_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 5)) { $idle_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 6)) { $iowait_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 7)) { $irq_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 8)) { $softirq_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 9)) { $steal_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 10)) { $guest_2 .= $value_2[$i]; }
			if (($value_2[$i] != " ") AND ($variable == 11)) { $guest_nice_2 .= $value_2[$i]; }
		}

		$active = ($user_2 - $user_1) + ($system_2 - $system_1) + ($iowait_2 - $iowait_1);
		$total = $active + ($idle_2 - $idle_1);
		$cpu = round((($active*100)/$total),2);
		$string_out = "CPU % usage >> ".$cpu;
		$value_out = $cpu;
		$pnp_out = "%=".$cpu;
	}


	if ($mode == "cpu_pro") {
		#     user    nice   system  idle      iowait irq   softirq  steal  guest  guest_nice
		#cpu  74608   2520   24433   1117073   6176   4054  0        0      0      0
		$command = "cat /proc/stat | grep 'cpu '";
		$value = command($command,$connect,$host);
		$value = command($command,$connect,$host);
		$user = "";
		$nice = "";
		$system = "";
		$idle = "";
		$iowait = "";
		$irq = "";
		$softirq = "";
		$steal = "";
		$guest = "";
		$guest_nice = "";
		$variable = 1;
		for ($i = 1; $i < strlen($value); $i++) {
			if (($value[$i] != " ") AND ($value[$i-1] == " ")) { $variable++; }
			if (($value[$i] != " ") AND ($variable == 2)) { $user .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 3)) { $nice .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 4)) { $system .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 5)) { $idle .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 6)) { $iowait .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 7)) { $irq .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 8)) { $softirq .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 9)) { $steal .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 10)) { $guest .= $value[$i]; }
			if (($value[$i] != " ") AND ($variable == 11)) { $guest_nice .= $value[$i]; }
		}
		$cpu_total = $user+$nice+$system+$idle+$iowait+$irq+$softirq+$steal+$guest+$guest_nice;
		$user = round(($user*1)/($cpu_total/100),2);
		$nice = round(($nice*1)/($cpu_total/100),2);
		$system = round(($system*1)/($cpu_total/100),2);
		$idle = round(($idle*1)/($cpu_total/100),2);
		$iowait = round(($iowait*1)/($cpu_total/100),2);
		$irq = round(($irq*1)/($cpu_total/100),2);
		$softirq = round(($softirq*1)/($cpu_total/100),2);
		$steal = round(($steal*1)/($cpu_total/100),2);
		$guest = round(($guest*1)/($cpu_total/100),2);
		$guest_nice = round(($guest_nice*1)/($cpu_total/100),2);
	
		$string_out = "CPU user=".$user."%,nice=".$nice."%,system=".$system."%,idle=".$idle."%,iowait=".$iowait."%,irq=".$irq."%,softirq=".$softirq."%,steal=".$steal."%,guest=".$guest."%,guest_nice=".$guest_nice;
		$value_out = $user;
		$pnp_out = "user%=".$user." nice%=".$nice." system%=".$system." idle%=".$idle." iowait%=".$iowait." irq%=".$irq." softirq%=".$softirq." steal%=".$steal." guest%=".$guest." guest_nice%=".$guest_nice;
	}

	if ($mode == "cpu_pro2") {
		$command = "top -n 2 | grep \"Cpu(s):\" | tail -n1";
		$value = command($command,$connect,$host);
		$find_notes = array("Cpu(s):"," ","us","sy","ni","id","wa","hi","si","st","\n",chr(37));
		$value = str_replace($find_notes,"",$value);
#		echo "\n".$value."\n";
		$cpu_user = "";
		$nice = "";
		$system = "";
		$idle = "";
		$iowait = "";
		$irq = "";
		$softirq = "";
		$steal = "";
		$guest = "";
		$guest_nice = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == ",") { 
				$variable++;
			} else {
				if ($variable == 1) { $cpu_user .= $value[$i]; }
				if ($variable == 2) { $system .= $value[$i]; }
				if ($variable == 3) { $nice .= $value[$i]; }
				if ($variable == 4) { $idle .= $value[$i]; }
				if ($variable == 5) { $iowait .= $value[$i]; }
				if ($variable == 6) { $irq .= $value[$i]; }
				if ($variable == 7) { $softirq .= $value[$i]; }
				if ($variable == 8) { $steal .= $value[$i]; }
				if ($variable == 9) { $guest .= $value[$i]; }
				if ($variable == 10) { $guest_nice .= $value[$i]; }
			}
		}
		if ($guest == "") { $guest = round(("3.1" + 0),4); }
		if ($guest_nice == "") { $guest_nice = "0.0"; }
		$string_out = "CPU user=".$cpu_user."%,system=".$system."%,nice=".$nice."%,idle=".$idle."%,iowait=".$iowait."%,irq=".$irq."%,softirq=".$softirq."%,steal=".$steal."%,guest=".$guest."%,guest_nice=".$guest_nice;
		$value_out = $user;
		$pnp_out = "user%=".$cpu_user." system%=".$system." nice%=".$nice." idle%=".$idle." iowait%=".$iowait." irq%=".$irq." softirq%=".$softirq." steal%=".$steal." guest%=".$guest." guest_nice%=".$guest_nice;
	}

	if ($mode == "dht") {
		$command = "cat ".$suboption." | tail -1";
		$value = command($command,$connect,$host);
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
		if ($option == "t") {
			$string_out = "Temperature (Celsius) >> ".$temperatura;
			$value_out = $temperatura;
			$pnp_out = "Celsius=".$value_out.";".$warning.";".$critical;
		}
		if ($option == "h") {
			$string_out = "Humidity (%) >> ".$humedad;
			$value_out = $humedad;
			$pnp_out = "%=".$value_out.";".$warning.";".$critical;
		}
	}

	if ($mode == "file_date") {
		$time_now = date("U");
		$command = "stat -c %Y ".$option;
		$value = command($command,$connect,$host);
		$time = date("Y-m-d H:i:s", ($value*1));
		$value_out = $time_now-($value*1);
		$string_out = "File ".$option." last modify ".$time." Seconds last modify >> ".$value_out;
		$pnp_out = "seconds_last_modify=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "file_size") {
		$command = "stat -c %s ".$option;
		$value = command($command,$connect,$host);
		$value_out = round(($value/1024),0);
		$string_out = "File ".$option." size in Kb >> ".$value_out;
		$pnp_out = "size_kb=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "file_size_bytes") {
		$command = "stat -c %s ".$option;
		$value = command($command,$connect,$host);
		$value_out = $value*1;
		$string_out = "File ".$option." size in bytes >> ".$value_out;
		$pnp_out = "size_bytes=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "interface") {
		$command = "cat /proc/net/dev | grep \"".$option."\" | awk \"{print \\$2}\"\"{print \\$10}\" ; sleep 10 ; cat /proc/net/dev | grep \"".$option."\" | awk \"{print \\$2}\"\"{print \\$10}\"";
		$value = command($command,$connect,$host);
		$value = str_replace("\n",";",$value);
		$in_1 = "";
		$in_2 = "";
		$out_1 = "";
		$out_2 = "";
		$variable = 1;
		for ($i = 0; $i < strlen($value); $i++) {
			if ($value[$i] == ";") { $variable++; }
			if (($value[$i] != ";") AND ($variable == 1)) { $in_1 .= $value[$i]; }
			if (($value[$i] != ";") AND ($variable == 2)) { $out_1 .= $value[$i]; }
			if (($value[$i] != ";") AND ($variable == 3)) { $in_2 .= $value[$i]; }
			if (($value[$i] != ";") AND ($variable == 4)) { $out_2 .= $value[$i]; }
		}
		$in = round(((($in_2-$in_1)/10)/1024),0);
		$out = round(((($out_2-$out_1)/10)/1024),0);
		$value_out = round(($in+$out),0);
		$string_out = "Bandwidth ".$option." IN=".$in."Kb/sec OUT=".$out."Kb/sec, Total bandwith in Kb/sec >> ".$value_out;
		$pnp_out = "Total_Kb_sec=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "zimbra") {
		$command = "sudo -u zimbra /opt/zimbra/bin/zmcontrol status | grep -e 'Stopped' | wc -l";
		$value = command($command,$connect,$host);
		$value_stoped = $value*1;

		$command = "sudo -u zimbra /opt/zimbra/bin/zmcontrol status| grep -e 'Running' | wc -l";
		$value = command($command,$connect,$host);
		$value_running = $value*1;
		$value_out = $value_stoped;
		$string_out = "Number of processes running ".$value_running." and stopped process >> ".$value_out;
		$pnp_out = "errors=".$value_out.";".$warning.";".$critical;
	}

	if ($mode == "ping") {
		$command = "ping -c 4 ".$option." | grep -e '100.0%' | wc -l";
		$value = command($command,$connect,$host);
		$value = $value*1;
		if ($value == 0) { $value_out = 1; }
		else {
			if ($value == 1) { $value_out = 0; }
		}
		$string_out = "Status PING to ".$option." (1=OK 0=Packet loss) >> ".$value_out;
		$pnp_out = "status=".$value_out.";".$warning.";".$critical;
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