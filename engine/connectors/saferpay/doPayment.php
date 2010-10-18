<?php
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

doPayment.php

**********************************************************/

$path = "../";	 
include("saferpay.class.php");	 

/*Enter your E-Mail for debugging*/
$debug = "";  
$payment = new saferpayPayment($debug,"../");										

if ($payment->sSYSTEM->sLanguage == 1) include("language_de.php");
else include("language_en.php");

$serverName = $payment->sSYSTEM->sCONFIG["sBASEPATH"];
$url= "http://".$serverName."/";

/*Check user sign-in*/
if (!$payment->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
	/*Redirecting to Viewport sale.*/ 
	$location = $url.$payment->config["sBASEFILE"].'/sViewport,sale';
	header("Location: $location");
	exit();
}

/*Check Terms*/
if ($payment->config['sIGNOREAGB']!="1") {

	if (!$_POST["sAGB"]){	
		$payment->sSYSTEM->_SESSION['sAGB'] = 'error';	
		/*Redirecting to Viewport sale.*/ 
		$location = $url.$payment->config["sBASEFILE"].'/sViewport,sale';
		header("Location: $location");
		exit();
	} else {
		$payment->sSYSTEM->_SESSION['sAGB'] = '';
	}
}

/*Init user data*/
$payment->initUser();
$userData = $payment->sUser;

	// **************************************************
	// * Looks into your PHP-configuration 
	// *
	// * Stops script immediately if CURL is not available
	// * Info will be used for accessing available XML-functions later also
	// **************************************************
	$arm = get_loaded_extensions(); 
		
	if(!in_array("curl",$arm)) { die($sLang["saferpay"]["curlNotInstalled"]); }


	// ************************************************** 
	// *
	// * Definitions 
	// *
	// **************************************************

	// **************************************************
	// * Get the own webserver’s self URL 
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
	// * Get this scripts web-address 
	// **************************************************

	$self_url_script = $self_protocol . $payment->sSYSTEM->sCONFIG["sBASEPATH"] . '/engine/connectors/saferpay/doPayment.php'; 
	
	// **************************************************
	// * Get this scripts web-folder  
	// * (needed for SUCCESS-/FAIL-/BACK- returnlinks below)
	// **************************************************

	$self_url_folder = $self_protocol . $payment->sSYSTEM->sCONFIG["sBASEPATH"] . '/engine/connectors/saferpay/'; 



	// **************************************************
	// *
	// * End definitions 
	// *
	// **************************************************


	// **************************************************
	// * Constant: The hosting gateway URL to create payinit URL 
	// **************************************************

	$saferpay_payinit_gateway = "https://www.saferpay.com/hosting/CreatePayInit.asp"; 


	// **************************************************
	// *
	// * Set the attributes
	// *
	// **************************************************
	// * Basic attributes
	// **************************************************

	// **************************************************
	// * Get amount and corrency from basket and generate unique bookingId
	// **************************************************
	$value = $payment->getAmount();			
	$amount = $payment->formatAmountCent($value);
	$paymentAmount=$amount;
	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
	$bookingId = substr(md5(uniqid(rand())),0,10);  

	$saferpayAccountID = $payment->saferpayAccountID;
	$saferpayCVC = $payment->saferpayCVC;
	$saferpayCardholder = $payment->saferpayCardholder;
	$saferpayMenucolor = $payment->saferpayMenucolor;
	$saferpayMenufontcolor = $payment->saferpayMenufontcolor;
	$saferpayBodyfontcolor = $payment->saferpayBodyfontcolor;
	$saferpayBodycolor = $payment->saferpayBodycolor;
	$saferpayHeadfontcolor = $payment->saferpayHeadfontcolor;
	$saferpayHeadcolor = $payment->saferpayHeadcolor;
	$saferpayHeadlinecolor = $payment->saferpayHeadlinecolor;
	$saferpayLinkcolor = $payment->saferpayLinkcolor;

	if ($saferpayCVC == 0) $saferpayCVC = "no";
	else $saferpayCVC = "yes";

	if ($saferpayCardholder == 0) $saferpayCardholder = "no";
	else $saferpayCardholder = "yes";


	$saferpayDescription  = $sLang["saferpay"]["saferpayDescription"] ;

	// * Check basket 
	$basket = $payment->getBasket();

	// * If basket is empty EXIT 
	if (!$basket["content"][0] || $amount<=0){
		echo $sLang["saferpay"]["order"]."<br /><a href=\"javascript:history.back();\">".$sLang["saferpay"]["back"]."</a>";
		exit();
	}

	$accountid = $saferpayAccountID; 	// the saferpay account id (for testing: the Saferpay testaccount-id)
	$orderid = $bookingId; 			// order or basket identifier (unique, dynamically defined from your shop)
	$amount = $paymentAmount; 			// the total amount for this payment (calculated from your shop)
	$currency = $currency; 			// the currency for this payment (defined from your shop)

	// * DESCRIPTION – attribute has to be html-encoded to verify html-conformity of this info-text 

	$description = htmlentities($saferpayDescription); 

	// * The result-links back from saferpay to this server
	// * Additionally URL-encoded for safe adding into resulting hosting-URL 

	$sessionID = session_id(); 
	
	// * secret Key 
	$sKey = md5("po0aEs83x".$sessionID.$accountid.$bookingId."Kra8s0Ua028X");
	$custom = $sessionID."-".$bookingId."-".$payment->sSYSTEM->sLanguage."-".$payment->sSYSTEM->sCurrency["id"]."-".$payment->sSYSTEM->_SESSION["sSubShop"]["id"]."-".$payment->sSYSTEM->_SESSION["sDispatch"]."-".$sKey;

	$successlink = $self_url_folder . "doCheckoutPayment.php?custom=".$custom; // return URL if payment successful
	$faillink = $self_url_folder . "fail.php";	// return URL if payment failed
	$backlink = $url.$payment->config["sBASEFILE"].'/sViewport,sale'; 	// return URL if user cancelled the payment 

	// **************************************************
	// *
	// * End set the attributes 
	// *
	// **************************************************


	// **************************************************
	// *
	// * Put all attributes together…
	// * for hosting: each attribute which could have non-url-conform characters inside should be urlencoded before
	// *
	// **************************************************
	// * Mandatory attributes
	// **************************************************

	$attributes = "?ACCOUNTID=" . $accountid;
	$attributes .= "&AMOUNT=" . $amount;
	$attributes .= "&CURRENCY=" . $currency;
	$attributes .= "&DESCRIPTION=" . urlencode($description);
	$attributes .= "&SUCCESSLINK=" . urlencode($successlink);
	$attributes .= "&FAILLINK=" . urlencode($faillink);
	$attributes .= "&BACKLINK=" . urlencode($backlink);
	
	// **************************************************
	// * Additional attributes
	// **************************************************

	$attributes .= "&CCCVC=".$saferpayCVC; // input of cardsecuritynumber mandatory
	$attributes .= "&CCNAME=".$saferpayCardholder; // input of cardholder name mandatory
	
	$attributes .= "&MENUCOLOR=".$saferpayMenucolor; 
	$attributes .= "&MENUFONTCOLOR=".$saferpayMenufontcolor; 
	$attributes .= "&BODYFONTCOLOR=".$saferpayBodyfontcolor; 
	$attributes .= "&BODYCOLOR=".$saferpayBodycolor; 
	$attributes .= "&HEADFONTCOLOR=".$saferpayHeadfontcolor; 
	$attributes .= "&HEADCOLOR=".$saferpayHeadcolor; 
	$attributes .= "&HEADLINECOLOR=".$saferpayHeadlinecolor; 

	// **************************************************
	// * Important (but optional) attributes
	// **************************************************

	$attributes .= "&ORDERID=" . $orderid; // input of cardsecuritynumber mandatory

	// **************************************************
	// * …and create hosting PayInit URL 
	// **************************************************

	$payinit_url = $saferpay_payinit_gateway . $attributes; 

	// **************************************************
	// *
	// * Get the Payment URL from the saferpay hosting server 
	// *
	// **************************************************
	// Initialize CURL session
	// **************************************************

	$cs = curl_init($payinit_url);

	// **************************************************
	// * Set CURL-session options
	// **************************************************

	curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
	curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
	curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
	curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true); 	// receive returned characters

	// **************************************************
	// * Execute CURL-session
	// **************************************************

	$payment_url = curl_exec($cs);

	// **************************************************
	// * End CURL-session
	// **************************************************
	
	$ce = curl_error($cs);
	curl_close($cs); 

	// **************************************************
	// Stop if php-curl is not working
	// **************************************************

	if( strtolower( substr( $payment_url, 0, 36 ) ) != "https://www.saferpay.com/vt/pay.asp?" ) {
		$msg = "<h1>".$sLang["saferpay"]["curlNotWorking"]."</h1>\r\n";
		$msg .= "<h2><font color=\"red\">".htmlentities($payment_url)."&nbsp;</font></h2>\r\n";
		$msg .= "<h2><font color=\"red\">".htmlentities($ce)."&nbsp;</font></h2>\r\n";
		die($msg);
	}

	// **************************************************
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// * 
	// * If you reach this line, you created the URL 
	// * for the customer's browser to reach 
	// * the Saferpay-VirtualTerminal
	// * 
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// **************************************************

	header("Location: $payment_url");

?>