<?php 
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

doCheckoutPayment.php

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

/*Init user data*/
$payment->initUser();
$userData = $payment->sUser;

$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";

$custom_org = urldecode($_REQUEST['custom']);
$custom = explode("-",$custom_org);	//Custom Array

$coreID = $custom[0];
$trans_id = $custom[1];
$skey = $custom[6];

/*Init Shopware param*/
$_REQUEST["trans_id"] = $trans_id;
$_REQUEST["coreID"] 	= $custom[0];	//CoreID / Session
$_REQUEST["uniqueID"] 	= $custom[1];	//BookingID
$_REQUEST["sLanguage"]	= $custom[2];	//Language
$_REQUEST["sCurrency"] 	= $currency;	//Currency
$_REQUEST["sSubShop"] 	= intval($custom[4]);	//Subshop-ID
$_REQUEST["dispatchID"] = intval($custom[5]);	//Dispatch
$_REQUEST["sComment"] = "";	

$saferpayAccountID = $payment->saferpayAccountID;
$saferpayPassword = $payment->saferpayPassword;
$saferpayTestsystem = $payment->saferpayTestsystem;
$saferpayAuthorization = $payment->saferpayAuthorization;


	// **************************************************
	// * Security Check-1
	// * Looks if BookingID or SessionID was manupulated
	// **************************************************

	// * Generate sKeyVerify Hash 
	$sKeyVerify = md5("po0aEs83x".$custom[0].$saferpayAccountID.$custom[1]."Kra8s0Ua028X");
	
	// * sKey must equal sKeyVerify 
	if ($skey != $sKeyVerify) {
		die($sLang["saferpay"]["sKeyFailed"]);
	}

// **************************************************
// *
// * PHP-CURL Saferpay
// *
// * Called after successful authorization from the Saferpay-Virtual Terminal (VT)
// *
// **************************************************

// ************************************************** 
// *
// * Definitions 
// *
// **************************************************

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
// * End definitions 
// *
// **************************************************


	// **************************************************
	// *
	// * Payment Confirmation
	// *
	// * (Authorization OK?)
	// *
	// **************************************************


	// **************************************************
	// * Constant: the hosting gateway URL to VerifyPayConfirm: 
	// * Check the returned paramter, avoid manipulation 
	// **************************************************

	$saferpay_payconfirm_gateway = "https://www.saferpay.com/hosting/VerifyPayConfirm.asp"; 


	// **************************************************
	// * Mandatory attributes
	// **************************************************

	$vt_data = $_GET["DATA"];
	$vt_signature = $_GET["SIGNATURE"];

	// * catch magic_quotes_gpc is set to yes in PHP.ini
	if( substr($vt_data, 0, 15) == "<IDP MSGTYPE=\\\"" ) {
		$vt_data = stripslashes($vt_data);
	}	

	// **************************************************
	// * Put all attributes together and create hosting PayConfirm URL 
	// * For hosting: each attribute which could have non-url-conform characters inside should be urlencoded before
	// **************************************************

	$payconfirm_url = $saferpay_payconfirm_gateway . "?DATA=" . urlencode($vt_data) . "&SIGNATURE=" . urlencode($vt_signature);
	
	// **************************************************
	// * Get the Payment URL from the saferpay hosting server 
	// **************************************************
	// * Initialize CURL session
	// **************************************************

	$cs = curl_init($payconfirm_url);

	// **************************************************
	// * Set CURL-session options
	// **************************************************

	curl_setopt($cs, CURLOPT_PORT, 443);		// set option for outgoing SSL requests via CURL
	curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false); // ignore SSL-certificate-check - session still SSL-safe
	curl_setopt($cs, CURLOPT_HEADER, 0);	// no header in output
	curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true); // receive returned characters

	// **************************************************
	// * Execute CURL-session
	// **************************************************

	$verification = curl_exec($cs);

	// **************************************************
	// * End CURL-session
	// **************************************************

	curl_close($cs); 

	// **************************************************
	// * Stop if verification not successful is not working
	// **************************************************

	if( strtoupper( substr( $verification, 0, 3 ) ) != "OK:" ) {
		
		die($sLang["saferpay"]["confirmationFailed"]."<br/>$verification");
	}

	// **************************************************
	// *
	// * Additional Verification
	// *
	// **************************************************
	// * Please regard:
	// * You can reach fully security if you compare additionally the attributes 
	// * accountid, amount, currency and orderid (if orderid was used)
	// * with the values which were used for the Saferpay PayInit-call before (see start.php)
	// **************************************************

	// **************************************************
	// * Used values in start.php (you should do it dynamically, of course)
	// **************************************************

	$value = $payment->getAmount();			
	$amount = $payment->formatAmountCent($value);
	$paymentAmount=$amount;

	// * Check basket 
	$basket = $payment->getBasket();

	// * If basket is empty EXIT 
	if (!$basket["content"][0] || $amount<=0){
		echo $sLang["saferpay"]["order"]."<br /><a href=\"javascript:history.back();\">".$sLang["saferpay"]["back"]."</a>";
		exit();
	}

	$start_accountid = $saferpayAccountID;
	$start_amount = $paymentAmount; 
	$start_currency = $currency; 
	$start_orderid = $trans_id;

	// **************************************************
	// * Now check with the XML-attributes inside DATA
	// **************************************************
	// * Use XML inside DATA
	// * (Please use one of the following XML-parsing types - which exist on your system) 
	// **************************************************
	if(in_array("dom",$arm))
	{
		
		$vt_xml = new DOMDocument();
		$vt_xml->loadXML($vt_data);
		
		// **************************************************
 		// * Compare values
		// **************************************************^
		if( $vt_xml->documentElement->getAttribute("ACCOUNTID") != $start_accountid ) {
			die($sLang["saferpay"]["wrongAccountID"]);
		}

		if( $vt_xml->documentElement->getAttribute("AMOUNT") != $start_amount ) {
			die($sLang["saferpay"]["wrongAmount"]);
		}
		if( $vt_xml->documentElement->getAttribute("CURRENCY") != $start_currency ) {
			die($sLang["saferpay"]["wrongCurrency"]);
		}
		
		/*
		if( $vt_xml->documentElement->getAttribute("ORDERID") != $start_orderid ) {
			die($sLang["saferpay"]["wrongOrder"]);
		}
		*/
			
		$saferpayID = $vt_xml->documentElement->getAttribute("ID");
		$saferpayToken = $vt_xml->documentElement->getAttribute("TOKEN");
		$saferpayProviderID = $vt_xml->documentElement->getAttribute("PROVIDERID");
		$saferpayProviderName = $vt_xml->documentElement->getAttribute("PROVIDERNAME");
		$saferpayAccountID = $vt_xml->documentElement->getAttribute("ACCOUNTID");
		$saferpayAmount = $vt_xml->documentElement->getAttribute("AMOUNT");
		$saferpayCurrency = $vt_xml->documentElement->getAttribute("CURRENCY");
		
		$eci = $vt_xml->documentElement->getAttribute("ECI");
				
	}
	else if(in_array("domxml",$arm))	// DOMXML instead
	{

	  	$vt_xmldoc = domxml_open_mem($vt_data);
  		$vt_xml = $vt_xmldoc->document_element();
		// **************************************************
 		// * Compare values
		// **************************************************
		if( $vt_xml->get_attribute("ACCOUNTID") != $start_accountid ) {
			die($sLang["saferpay"]["wrongAccountID"]);
		}
		if( $vt_xml->get_attribute("AMOUNT") != $start_amount ) {
			die($sLang["saferpay"]["wrongAmount"]);
		}
		if( $vt_xml->get_attribute("CURRENCY") != $start_currency ) {
			die($sLang["saferpay"]["wrongCurrency"]);
		}
		if( $vt_xml->get_attribute("ORDERID") != $start_orderid ) {
			die($sLang["saferpay"]["wrongOrder"]);
		}

		$saferpayID = $vt_xml->get_attribute("ID");
		$saferpayToken = $vt_xml->get_attribute("TOKEN");
		$saferpayProviderID = $vt_xml->get_attribute("PROVIDERID");
		$saferpayProviderName = $vt_xml->get_attribute("PROVIDERNAME");
		$saferpayAccountID = $vt_xml->get_attribute("ACCOUNTID");
		$saferpayAmount = $vt_xml->get_attribute("AMOUNT");
		$saferpayCurrency = $vt_xml->get_attribute("CURRENCY");

		$eci = $vt_xml->get_attribute("ECI");
	}
	else if(in_array("SimpleXML",$arm))	// SimpleXML instead
	{

		$vt_xml = new SimpleXMLElement($vt_data);
		// **************************************************
 		// * Compare values
		// **************************************************
		if( $vt_xml["ACCOUNTID"] != $start_accountid ) {
			die($sLang["saferpay"]["wrongAccountID"]);
		}
		if( $vt_xml["AMOUNT"] != $start_amount ) {
			die($sLang["saferpay"]["wrongAmount"]);
		}
		if( $vt_xml["CURRENCY"] != $start_currency ) {
			die($sLang["saferpay"]["wrongCurrency"]);
		}
		if( $vt_xml["ORDERID"] != $start_orderid ) {
			die($sLang["saferpay"]["wrongOrder"]);
		}

		$saferpayID = $vt_xml["ID"];
		$saferpayToken = $vt_xml["TOKEN"];
		$saferpayProviderID = $vt_xml["PROVIDERID"];
		$saferpayProviderName = $vt_xml["PROVIDERNAME"];
		$saferpayAccountID = $vt_xml["ACCOUNTID"];
		$saferpayAmount = $vt_xml["AMOUNT"];
		$saferpayCurrency = $vt_xml["CURRENCY"];

		$eci = $vt_xml["ECI"];
	}
	else // No XML available - parse native
	{

		// delete starting and trailing markes <IDP .... />
		$data = ereg_replace("^<IDP( )*", "", $vt_data);
		$data = ereg_replace("( )*/( )>$", "", $data);
		$data = trim($data);
		$vt_xml = array();
		while(strlen($data) > 0)
		{
			$pos = strpos($data, "=\"");
			$name = substr($data, 0, $pos); // get attribute name
			$data = substr($data, $pos + 2); // skip ="
			$pos = strpos($data, "\"");
			$value = substr($data, 0, $pos); // get attribute value 
			$data = substr($data, $pos + 1); // skip "
			$data = trim($data);
			$vt_xml[$name] = $value;
		}
		// **************************************************
 		// * Compare values
		// **************************************************
		if( $vt_xml["ACCOUNTID"] != $start_accountid ) {
			die($sLang["saferpay"]["wrongAccountID"]);
		}
		if( $vt_xml["AMOUNT"] != $start_amount ) {
			die($sLang["saferpay"]["wrongAmount"]);
		}
		if( $vt_xml["CURRENCY"] != $start_currency ) {
			die($sLang["saferpay"]["wrongCurrency"]);
		}
		if( $vt_xml["ORDERID"] != $start_orderid ) {
			die($sLang["saferpay"]["wrongOrder"]);
		}

		$saferpayID = $vt_xml["ID"];
		$saferpayToken = $vt_xml["TOKEN"];
		$saferpayProviderID = $vt_xml["PROVIDERID"];
		$saferpayProviderName = $vt_xml["PROVIDERNAME"];
		$saferpayAccountID = $vt_xml["ACCOUNTID"];
		$saferpayAmount = $vt_xml["AMOUNT"];
		$saferpayCurrency = $vt_xml["CURRENCY"];

		$eci = $vt_xml["ECI"];
	}


	// ******** LIABILITY-SHIFT  ************************
	// PLEASE REGARD - ATTENTION  ***********************
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// *  if $eci == "" or $eci  == "0" -> No liability-shiift for this transaction was given.
	// * (or account is not 3DSecure-enabled)
	// *  Merchant has to decide for this case if to continue on own risk or not.
	// *  (For cases of ECI='2' please ask your acquirer about eventually additional restrictions for liability-shift) 
	// *  Please regard - liability-shift also only possible/functional for Visa and MasterCard
	// ...
	$ecimsg = "";
	switch($eci)
	{
		case "2":
			$ecimsg = $sLang["saferpay"]["eci2"]; 
			break;
		case "1":
			$ecimsg = $sLang["saferpay"]["eci1"];
			break;
		default:
			$eci = urlencode($eci);
			$ecimsg = $sLang["saferpay"]["eci"]." ".$eci." ".$sLang["saferpay"]["liability"];
			break;
	}

	// **************************************************
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// *
	// * If you reach this line - the payment-authorization is fully ok and verificated
	// *
	// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	// **************************************************

	// **************************************************
	// * Security Check-2
	// * Looks if BookingID is already booked
	// **************************************************


	$sql = "SELECT saferpay_id FROM saferpay_orders WHERE saferpay_id='$saferpayID'";	
	$result = $payment->sDB_CONNECTION->GetRow($sql);
	$dbSaferpayID = $result[0];
	
	if ($dbSaferpayID == $saferpayID) {
		die($sLang["saferpay"]["SaferpayID"]);		
	}


	if( substr(	$start_accountid, 0, 6) == "99867-" AND $saferpayTestsystem == "1" ) {
		$ecimsgTest = $sLang["saferpay"]["test"]." ";
	}

	// **************************************************
	// * Parse ID and TOKEN out of $verification from Saferpay-Call VerifyPayConfirm
	// **************************************************

	$vpc = array();
	parse_str( substr( $verification, 3), $vpc ); 


	// **************************************************
	// * Mandatory attributes
	// **************************************************

	$vt_id = $vpc["ID"];
	$vt_token = $vpc["TOKEN"];
	$saferpayID = $vt_id;

	// **************************************************
	// *
	// * Payment Capturing
	// *
	// **************************************************
	// * If you want directly to capture the amount of a successful authorization
	// * you could capture it as described directly after "back-from-authorization" 
	// * See the sample next steps:
	// **************************************************
	
	// **************************************************
	// * Set reservation yes or no  
	// **************************************************

	if (($saferpayAuthorization == 1))$reservation = 1;
	else $reservation = 0; 
	
	// **************************************************
	// * No reservation by Giropay payment 
	// **************************************************
	
	$pos = strpos(strtoupper($saferpayProviderName), "GIROPAY");
	
	if($pos !== false) {
		$reservation = 0;
		$ecimsg = $ecimsgTest.$sLang["saferpay"]["paymentBy"]." ".$saferpayProviderName;
	} else {
		$ecimsg = $ecimsgTest.$sLang["saferpay"]["paymentBy"]." ".$saferpayProviderName." ".$ecimsg;
	} 
	
	if($reservation == 0) {
	
		// **************************************************
		// * Constant: the hosting gateway URL to PayComplete 
		// **************************************************
	
		$saferpay_paycomplete_gateway = "https://www.saferpay.com/hosting/PayComplete.asp";
	
		// **************************************************
		// * Put all attributes together and create hosting PayComplete URL 
		// * For hosting: each attribute which could have non-url-conform characters inside should be urlencoded before
		// **************************************************
	
		$paycomplete_url = $saferpay_paycomplete_gateway . "?ACCOUNTID=" . $start_accountid; 
		$paycomplete_url .= "&ID=" . urlencode($vt_id) . "&TOKEN=" . urlencode($vt_token);
		
	
		// **************************************************
		// * Special for testaccount: Passwort for hosting-capture neccessary.
		// * Not needed for standard-saferpay-eCommerce-accounts
		// **************************************************
		
		if( (substr($start_accountid, 0, 6) == "99867-") AND ($saferpayTestsystem == "1") ) {
			$paycomplete_url .= "&spPassword=".$saferpayPassword;
		}
		
		// **************************************************
		// * Call the Capture URL from the saferpay hosting server 
		// **************************************************
		// * Initialize CURL session
		// **************************************************
		
		$cs = curl_init($paycomplete_url);
		
		// **************************************************
		// Set CURL-session options
		// **************************************************
	
		curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
		curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
		curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
		curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true);	// receive returned characters
	
		// **************************************************
		// Execute CURL-session
		// **************************************************
	
		$answer = curl_exec($cs);
	
		// **************************************************
		// End CURL-session
		// **************************************************
	
		curl_close($cs); 
	
		// **************************************************
		// Stop if capture is not successful
		// **************************************************	
	
		
		if( strtoupper( $answer ) != "OK" ) {
			
			die($sLang["saferpay"]["captureFailed"]."<br/>$answer");
		}	
	
		// **************************************************
		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//
		// If you reach this line - the payment is complete
		//
		// Real money-transfer will be activated within the next batch-closure.
		// Either manually via Saferpay-Backoffice or automatically if 
		// configured in the account-setup of the Saferpay-Backoffice
		//
		// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// **************************************************

		$saferpayComplete = 1;
		$status = 12;		

	} else {
		$saferpayComplete = 0;
		$status = 18;		
		$vt_token = $vt_token;
		$answer = $verification;
		
	}

			
	$sql  = "INSERT INTO saferpay_orders (trans_id, orders_id, saferpay_account_id, saferpay_id, saferpay_token, saferpay_amount, saferpay_currency, saferpay_provider_id, saferpay_provider_name, saferpay_eci, saferpay_complete, saferpay_complete_result, date_added, last_modified) ";
	$sql .= "VALUES ";
	$sql .= "('','$trans_id','$saferpayAccountID','$saferpayID','$vt_token','$saferpayAmount','$saferpayCurrency','$saferpayProviderID','$saferpayProviderName', '$eci', '$saferpayComplete', '$answer', '".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."')";

	$payment->sDB_CONNECTION->Execute($sql);	
		
	$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",$status);
	
	if (empty($payment->config["sUSESSL"])){
    	$url= "http://".$payment->config["sBASEPATH"]."/";
    } else {
    	$url= "https://".$payment->config["sBASEPATH"]."/";
    }
	
	$success_url = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$trans_id.'/';					

	$sql  = "UPDATE s_order SET cleared = '$status', comment = '$ecimsg' WHERE transactionID = '$trans_id' LIMIT 1";
	$payment->sDB_CONNECTION->Execute($sql);		

	header("Location: ".$success_url);
	exit();

?>