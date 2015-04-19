#!/usr/bin/php
<?php
        $cadena = exec("curl -u nagios:NagiosOP5 'http://192.168.1.10/sadmin/GetSystemStatus.event' -s | grep -e 'CPU Temp'");
#       echo $cadena;       &deg; C
$longitud = strlen($cadena);
$x=1;
for ($i=0; $i<=($longitud-10) ;$i++){
 if (($cadena[$i] == "&") AND ($cadena[$i+1] == "d") AND ($cadena[$i+2] == "e") AND ($cadena[$i+3] == "g") AND ($cadena[$i+4] == ";") AND ($cadena[$i+5] == " ") AND ($cadena[$i+6] == "C")) {
   $temperatura[$x] = $cadena[$i-2].$cadena[$i-1];
   $x++;
 }
}
$temperatura_externa = $temperatura[1];
$temperatura_interna = $temperatura[2];

echo "Temp. Interna = ".$temperatura_interna." C - Temp. Externa = ".$temperatura_externa." C | temp_int=".$temperatura_interna.", temp_ext=".$temperatura_externa;

?>
