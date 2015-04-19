#!/usr/bin/php
<?php

$criticals = 0;
$warnings = 0;

$string = "";

$salida = 0;
$string_salida = "OK - ";

foreach ($argv as $arg) {
        if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
        if (strpos($arg, "v=") !== false) { $version = str_replace("p=","",$arg); }
        if (strpos($arg, "t=") !== false) { $community = str_replace("t=","",$arg); }

        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
		
}

$volum_name_oid = ".1.3.6.1.4.1.789.1.5.4.1.2";
$volum_name = snmpwalk("$host","$community",$volum_name_oid);

$volum_percent_oid = ".1.3.6.1.4.1.789.1.5.4.1.6";
$volum_percent = snmpwalk("$host","$community",$volum_percent_oid);

for ($i=0; $i<count($volum_name); $i++) {
		$eliminar = array("INTEGER: ","STRING: ","Timeticks: ","Counter32: ","Gauge32: ");
		$names = str_replace($eliminar,"",$volum_name[$i]);
		$percents = str_replace($eliminar,"",$volum_percent[$i]);
		$percents = $percents*1;
		if ($percents > $warning) {
			if ($percents > $critical) { $criticals++; } else { $warnings++; }
		}
        $string .= "Volum: ".$names." ".$percents."%\n";
}

#echo $criticals."-".$warnings;

if ($warnings > 0) { $salida = 1; $string_salida = "WARNING - "; }
if ($criticals > 0) { $salida = 2; $string_salida = "CRITICAL - "; }

echo $string_salida." Volums warning = ".$warnings." (".$warning."%), volums critical = ".$criticals." (".$critical."%)\n".$string." | warnings=".$warnings.";; criticals=".$criticals.";; \n";

exit($salida)


?>