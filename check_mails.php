#!/usr/bin/php
<?php
$server = "";
$user = "";
$pass = "";
$box = "";
$from = "";
$text = "";
$days = 15;

$mensajes = 0;
$warning = 0;
$critical = 0;

$salida_mensaje = "";
$lista_mails = "";
$salida = 0;

foreach ($argv as $arg) {
        if (strpos($arg, "s=") !== false) { $server = str_replace("s=","",$arg); }
        if (strpos($arg, "u=") !== false) { $user = str_replace("u=","",$arg); }
        if (strpos($arg, "p=") !== false) { $pass = str_replace("p=","",$arg); }
        if (strpos($arg, "b=") !== false) { $box = str_replace("b=","",$arg); }
        if (strpos($arg, "f=") !== false) { $from = str_replace("f=","",$arg); }
        if (strpos($arg, "t=") !== false) { $text = str_replace("t=","",$arg); }
        if (strpos($arg, "d=") !== false) { $days = str_replace("d=","",$arg); }
        if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
        if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

if (($server == "") OR ($box == "") OR ($server == "") OR ($pass == "")) {
        $salida_mensaje = "Not all parameters were introduced.";
        $salida = 1;
} else {
        $hostname = "{".$server."}".$box;
        $inbox = imap_open($hostname,$user,$pass,OP_READONLY) or die("Connect ERROR: ".imap_last_error());

        $date = date("d M Y", strToTime("-".$days." days"));
		if ($from == "") {
			$string = "SINCE \"".$date."\"";
		} else {
			$string = "FROM \"".$from."\" SINCE \"".$date."\"";
		}
        $emails = imap_search($inbox, $string, SE_FREE, "UTF-8");
        if($emails) {
			if ($text == "") {
				foreach($emails as $email_number) {
                    $overview = imap_fetch_overview($inbox,$email_number,0);
                    $lista_mails .= "\n"."Date: ".$overview[0]->date." Subject: ".$overview[0]->subject." From: ".$overview[0]->from;
					$mensajes++;
				}
			} else {
                foreach($emails as $email_number) {
					if (strpos(imap_qprint(imap_body($inbox,$email_number)), $text) !== false) {
						$overview = imap_fetch_overview($inbox,$email_number,0);
						$lista_mails .= "\n"."Date: ".$overview[0]->date." Subject: ".$overview[0]->subject." From: ".$overview[0]->from;
						$mensajes++;
					}
                }
			}
        }
        $salida_mensaje = "OK: ".$mensajes." mails found with <".$text.">.";
        if (($warning > 0) and ($warning <= $mensajes)) { $salida = 1; $salida_mensaje = "WARNING: ".$mensajes." mails found with <".$text.">."; }
        if (($critical > 0) and ($critical <= $mensajes)) { $salida = 2; $salida_mensaje = "CRITICAL: ".$mensajes." mails found with <".$text.">."; }
        imap_close($inbox);
}

echo $salida_mensaje." | mails=".$mensajes;
echo $lista_mails;
echo "\n";
exit($salida);

?>

