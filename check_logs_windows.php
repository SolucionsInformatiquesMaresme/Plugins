#!/usr/bin/php
<?php
$job = "";
$warning = "";
$critical = "";

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "fi=") !== false) { $file = str_replace("fi=","",$arg); }
	if (strpos($arg, "fo=") !== false) { $folder = str_replace("fo=","",$arg); }
	if (strpos($arg, "s=") !== false) { $string = str_replace("s=","",$arg); }
	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

$year = date("Y");
$month = date("m");
$day = date("d");
$file = str_replace("[YYYY]",$year,$file);
$file = str_replace("[MM]",$month,$file);
$file = str_replace("[DD]",$day,$file);


$forder_final = "/tmp/check_logs_windows_".rand(1000000,1999999);
$forder_remote = "//".$host."/".$folder;
shell_exec("mkdir ".$forder_final);
shell_exec("mount.cifs ".$forder_remote." ".$forder_final." -o cred=/opt/plugins/custom/check_logs_windows userdir_mode=0775 guid=1000 0 0");
$count_error = shell_exec("cat ".$forder_final."/".$file." | grep ".$string." | wc -l");
shell_exec("umount ".$forder_final);
shell_exec("rm -rf ".$forder_final);

$count_error = $count_error*1;
$estado = "OK";
$salida = 0;
if ($warning != "") { $warning = $warning*1; if ($count_error >= $warning ) { $estado = "WARNING"; $salida=1; }}
if ($critical != "") { $critical = $critical*1; if ($count_error >= $critical ) { $estado = "CRITICAL"; $salida=2; }}
echo $estado." - ".$count_error." coincidencias con el string <".$string."> | coincidencias=".$count_error.";".$warning.";".$critical."";


echo "\n";
exit($salida);

?>
