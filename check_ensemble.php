#!/usr/bin/php
<?php

$port = 57772;

$salida=0;

$help = false;
$version = false;

$warning = "";
$critical = "";
$warning_low = "";
$critical_low = "";

$value_out = 0;
$pnp_out = "";

$salida = 0;
$string_salida = "OK - ";

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $host = str_replace("H=","",$arg); }
	if (strpos($arg, "u=") !== false) { $user = str_replace("u=","",$arg); }
	if (strpos($arg, "p=") !== false) { $pass = str_replace("p=","",$arg); }
	if (strpos($arg, "P=") !== false) { $port = str_replace("P=","",$arg); }
	if (strpos($arg, "m=") !== false) { $mode = str_replace("m=","",$arg); }
	if (strpos($arg, "s=") !== false) { $smode = str_replace("s=","",$arg); }

	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
	if (strpos($arg, "W=") !== false) { $warning_low = str_replace("W=","",$arg); }
	if (strpos($arg, "C=") !== false) { $critical_low = str_replace("C=","",$arg); }

	if (strpos($arg, "-h") !== false) { $help = true; }
	if (strpos($arg, "-v") !== false) { $version = true; }
}

if ($mode == "GetSystem") { $webservice = "GetSystem"; }

#$url = "http://$host:$port/csp/sys/SYS.WSMon.Service.cls?soap_method=$webservice&CacheUserName=$user&CachePassword=$pass";
/*
Example from:
http://stackoverflow.com/questions/953639/connecting-to-ws-security-protected-web-service-with-php
Other examples on same webpage
*/
class WsseAuthHeader extends SoapHeader {
	private $wss_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
	function __construct($user, $pass, $ns = null) {
		if ($ns) {
			$this->wss_ns = $ns;
		}
		$auth = new stdClass();
		$auth->Username = new SoapVar($user, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns); 
		$auth->Password = new SoapVar($pass, XSD_STRING, NULL, $this->wss_ns, NULL, $this->wss_ns);
		$username_token = new stdClass();
		$username_token->UsernameToken = new SoapVar($auth, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns); 
		$security_sv = new SoapVar(
			new SoapVar($username_token, SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'UsernameToken', $this->wss_ns),
			SOAP_ENC_OBJECT, NULL, $this->wss_ns, 'Security', $this->wss_ns);
		parent::__construct($this->wss_ns, 'Security', $security_sv, true);
	}
}
$wsse_header = new WsseAuthHeader('nagios', 'Monitoritzacio2014');
$URL = "http://$host:$port/csp/sys/SYS.WSMon.Service.cls";
//URI is the default namespace in this non-wsdl mode (wsdl location is null)
$client = new SoapClient(null, array(
    'location' => $URL,
    'uri'      => "http://www.intersystems.com/cache/wsmon/1",
    'trace'    => 1,
    'exception'=> 0
    ));
$client->__setSoapHeaders(array($wsse_header));
$return = $client->__soapCall($webservice,array(),array('soapaction' => 'http://www.intersystems.com/cache/wsmon/1/'.$webservice));
#var_dump($return);

$value_out = $return->$smode;

$string_out = "Webservice \"".$webservice."\" element \"".$smode."\" return value >> ".$value_out;

if (($warning != "") or ($critical != "") or ($warning_low != "") or ($critical_low != "")) { $pnp_out = $smode; }
if ($warning != "") { if ($value_out >= $warning) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical != "") { if ($value_out >= $critical){ $salida = 2; $string_salida = "CRITICAL - "; } }
if ($warning_low != "") { if ($value_out <= $warning_low) { $salida = 1; $string_salida = "WARNING - "; } }
if ($critical_low != "") { if ($value_out <= $critical_low){ $salida = 2; $string_salida = "CRITICAL - "; } }
if ($pnp_out == "") {
	echo $string_salida.$string_out."\n";
} else {
	echo $string_salida.$string_out." | ".$pnp_out."=".$value_out.";".$warning.";".$critical."\n";
}

exit($salida);
?>
