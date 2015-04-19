#!/usr/bin/php
<?php

$salida=0;
$warning = 0;
$critical = 0;

foreach ($argv as $arg) {
        if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
        if (strpos($arg, "u=") !== false) { $user = str_replace("u=","",$arg); }
        if (strpos($arg, "p=") !== false) { $pass = str_replace("p=","",$arg); }
        if (strpos($arg, "C=") !== false) { $contador = str_replace("C=","",$arg); }
        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

$resultado = shell_exec("isql megacoco ".$user." ".$pass." < /opt/plugins/contadores.sql | grep ".$contador);

$contador_valor = "";
for ($i = 2; $resultado[$i] != " "; $i++) {
    $contador_valor .= $resultado[$i];
}

echo "TESIS tiene el contador ".$contador." a ".$contador_valor;

exit($salida);

?>
