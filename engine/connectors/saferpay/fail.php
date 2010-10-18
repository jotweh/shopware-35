<?php 
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

fail.php

**********************************************************/


// **************************************************
// *
// * NO SAFERPAY SPECIFIC COMMANDS INSIDE THIS SCRIPT !
// *
// **************************************************
// *
// * Called after denied/failed authorization from the Saferpay-Virtual Terminal (VT)
// *
// **************************************************

$path = "../";	 
include("saferpay.class.php");	 

/*Enter your E-Mail for debugging*/
$debug = "";  
$payment = new saferpayPayment($debug,"../");										
	
if ($payment->sSYSTEM->sLanguage == 1) include("language_de.php");
else include("language_en.php");

	// **************************************************
	// * Get the own webserver’s self URL for this testscript(s)
	// *
	// **************************************************
	// * Define protocol: check if own page is SSL-secured
	// **************************************************

	if (isset($_SERVER["HTTPS"])) {	
		if($_SERVER["HTTPS"] == "on") {
			$self_protocol = "https://";
		} else {
			$self_protocol = "http://"; 
		}
	} else {	
		$self_protocol = "http://"; 
	}

	// **************************************************
	// * Get this scripts web-folder by removing script’s filename 
	// * (needed for SUCCESS-/FAIL-/BACK- returnlinks below)
	// **************************************************

	$serverName = $payment->sSYSTEM->sCONFIG["sBASEPATH"];
	$self_url_folder= $self_protocol.$serverName."/engine/connectors/saferpay/";

	// **************************************************
	// * Get this scripts web-address 
	// **************************************************
	$self_url_script = $self_url_folder . "fail.php"; 

	// **************************************************
	// * Set the startup-script
	// **************************************************

	$checkout_url = $self_url_folder . "form.php"; 


?><html>
<head> 
	<title>Saferpay</title> 
	<link href="../../../templates/0/de/media/css/basic.css" rel="stylesheet" type="text/css" media="screen" />
	<!--[if lte IE 6]>
		<link href="../../../templates/0/de/media/css/lteie6.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="../../../templates/0/de/media/css/lteie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
</head>
<body> 
<style>
body {
font-size:12px;
font-family:Arial,Geneva,Arial,Helvetica,sans-serif;
}
</style>
<div style="width:600px; padding:10px 10px;text-align:left;">
	<h1><?PHP echo $sLang["saferpay"]["fail"]; ?></h1>
	<br><?PHP echo $sLang["saferpay"]["click"]." <u><a href=\"".$checkout_url."\">".$sLang["saferpay"]["here"]."</a></u> ".$sLang["saferpay"]["checkout"]; ?> 
</div>
</body>
</html>