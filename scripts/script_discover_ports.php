#!/usr/bin/php
<?php

foreach ($argv as $arg) {
        if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
        if (strpos($arg, "v=") !== false) { $version = str_replace("p=","",$arg); }
        if (strpos($arg, "c=") !== false) { $community = str_replace("c=","",$arg); }
}

$sysDescr = snmpget("$host","$community","system.sysDescr.0");


$ifIndex_oid = "1.3.6.1.2.1.2.2.1.1";
$ifIndex = snmpwalk("$host","$community",$ifIndex_oid);

$ifDescr_oid = "1.3.6.1.2.1.2.2.1.2";
$ifDescr = snmpwalk("$host","$community",$ifDescr_oid);

$ifAdminStatus_oid = "1.3.6.1.2.1.2.2.1.7";
$ifAdminStatus = snmpwalk("$host","$community",$ifAdminStatus_oid);

$ifOperStatus_oid = "1.3.6.1.2.1.2.2.1.8";
$ifOperStatus = snmpwalk("$host","$community",$ifOperStatus_oid);

$ifLastChange_oid = "1.3.6.1.2.1.2.2.1.9";
$ifLastChange = snmpwalk("$host","$community",$ifLastChange_oid);

$in_errors_oid = "1.3.6.1.2.1.2.2.1.14";
$in_errors = snmpwalk("$host","$community",$in_errors_oid);

$in_discards_oid = "1.3.6.1.2.1.2.2.1.13";
$in_discards = snmpwalk("$host","$community",$in_discards_oid);

$out_errors_oid = "1.3.6.1.2.1.2.2.1.20";
$out_errors = snmpwalk("$host","$community",$out_errors_oid);

$out_discards_oid = "1.3.6.1.2.1.2.2.1.19";
$out_discards = snmpwalk("$host","$community",$out_discards_oid);

$speed_oid = "1.3.6.1.2.1.2.2.1.5";
$speed = snmpwalk("$host","$community",$speed_oid);

for ($i=0; $i<count($ifIndex); $i++) {
		$eliminar = array("INTEGER: ","STRING: ","Timeticks: ","Counter32: ","Gauge32: ");
		$index = str_replace($eliminar,"",$ifIndex[$i]);
		$description = str_replace($eliminar,"",$ifDescr[$i]);
		$admin_status = str_replace($eliminar,"",$ifAdminStatus[$i]);
		$admin_oper = str_replace($eliminar,"",$ifOperStatus[$i]);
		$last_change = str_replace($eliminar,"",$ifLastChange[$i]);
		$input_errors = str_replace($eliminar,"",$in_errors[$i]);
		$input_discards = str_replace($eliminar,"",$in_discards[$i]);
		$output_errors = str_replace($eliminar,"",$out_errors[$i]);
		$output_discards = str_replace($eliminar,"",$out_discards[$i]);
		$port_speed = str_replace($eliminar,"",$speed[$i]);
        echo "Port index:\t\t".$index." (".$ifIndex_oid.".".$index.")\n";
        echo "Description:\t\t".$description." (".$ifDescr_oid.".".$index.")\n";
        echo "Admin status:\t\t".$admin_status." (".$ifAdminStatus_oid.".".$index.")\n";
        echo "Oper status:\t\t".$admin_oper." (".$ifOperStatus_oid.".".$index.")\n";
        echo "Last Change:\t\t".$last_change." (".$ifLastChange_oid.".".$index.")\n";
        echo "Input Errors:\t\t".$input_errors." (".$in_errors_oid.".".$index.")\n";
        echo "Input Discards:\t\t".$input_discards." (".$in_discards_oid.".".$index.")\n";
        echo "Output Errors:\t\t".$output_errors." (".$out_errors_oid.".".$index.")\n";
        echo "Output Discards:\t".$output_discards." (".$out_discards_oid.".".$index.")\n";
        echo "Port Speed:\t\t".$port_speed." (".$speed_oid.".".$index.")\n";
		echo "\n";
}           

?>