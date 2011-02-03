<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

doPaymentGuest.php

**********************************************************/

$path = "../";	
include("paypalexpress.class.php");	

/*Enter your E-Mail for debugging*/
$debug = ""; 
$payment = new paypalexpressPayment($debug,"../");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "1";

/*Include PayPal files*/
require_once 'CallerService.php';
require_once 'constants.php';

/*Get Basket*/
$basket = $payment->getBasket();

/*Generate unique Transaction-ID */
$bookingId = substr(md5(uniqid(rand(),true)),0,10);  

$payment->sSYSTEM->_SESSION['trans_id'] = $bookingId;

//*************************************************************************************
/* The servername and serverport tells PayPal where the buyer
   should be directed back to after authorizing payment.
   In this case, its the local webserver that is running this script
   Using the servername and serverport, the return URL is the first
   portion of the URL that buyers will return to after authorizing payment
   */

   $serverName = $payment->sSYSTEM->sCONFIG["sBASEPATH"];
   if (empty($payment->config["sUSESSL"])){
   	$url= "http://".$serverName."/";
   }else {
   	$url= "https://".$serverName."/";
   }
   

	/*Get amount and corrency from basket*/
	$amount =$basket['Amount'];

	/*Convert price to float*/ 			
	$amount = strtr ($amount, ',', '.' );
	$amount = (float) $amount;
	
	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";

	/*Set Paypal parameters*/
	$paymentAmount=$amount;
	$currencyCodeType=$currency;
	$paymentType= PAYMENT_TYPE;
	
	if (empty($basket['content'][0]) || $amount <= 0){
		echo $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressOrderAlreadySent'];
		exit;
	}
 
	$custom = session_id()."|".$bookingId."|".$payment->sSYSTEM->sLanguage."|".$payment->sSYSTEM->sCurrency["id"]."|".$payment->sSYSTEM->_SESSION["sSubShop"]["id"]."|".$payment->sSYSTEM->_SESSION["sDispatch"];
	$payment->sSYSTEM->_SESSION['customArray'] = $custom;


 /* The returnURL is the location where buyers return when a
	payment has been succesfully authorized.
	The cancelURL is the location buyers are sent to when they hit the
	cancel button during authorization of payment during the PayPal flow
	*/

	$returnURL = $url.'engine/connectors/paypalexpress/GetExpressCheckoutDetails.php?coreId='.session_id().'&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&paymentAmount='.$paymentAmount;
	
	$cancelURL = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sRefererAllowed,1/sCoreId,'.session_id().'/booking,'.$bookingId.'/?paymentType='.$paymentType;
    $giropaySuccessURL = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.session_id().'/sUniqueID,'.$bookingId.'/'; 
    $giropayCancelURL = $cancelURL;
    $giropayTXNPendingURL = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,paypalexpressTXNPending';
      
	$returnURL =urlencode($returnURL);
	$cancelURL =urlencode($cancelURL);
   	$giropaySuccessURL =urlencode($giropaySuccessURL);
   	$giropayCancelURL =urlencode($giropayCancelURL);
   	$giropayTXNPendingURL =urlencode($giropayTXNPendingURL);

 /* Construct the parameter string that describes the PayPal payment
	the varialbes were set in the web form, and the resulting string
	is stored in $nvpstr
	*/

	$LOCALECODE = Shopware()->Locale()->getRegion();
  
   $nvpstr="&Amt=".$paymentAmount."&PAYMENTACTION=".$paymentType."&ReturnUrl=".$returnURL."&CANCELURL=".$cancelURL ."&GIROPAYSUCCESSURL=".$giropaySuccessURL."&GIROPAYCANCELURL=".$giropayCancelURL."&BANKTXNPENDINGURL=".$giropayTXNPendingURL."&CURRENCYCODE=".$currencyCodeType."&LOCALECODE=".$LOCALECODE;


 /* Make the call to PayPal to set the Express Checkout token
	If the API call succeded, then redirect the buyer to PayPal
	to begin to authorize payment.  If an error occured, show the
	resulting errors
	*/

	/*Log PayPal Request*/
	if ($paypalLogging == "1") {
		$payment->sLog("Request SetExpressCheckout", $nvpstr, false);
	}
	
   $resArray=hash_call("SetExpressCheckout",$nvpstr);
  
   $payment->sSYSTEM->_SESSION['reshash']=$resArray;

	/*Log PayPal Response*/
	if ($paypalLogging == "1") {
		foreach($resArray as $schluessel => $wert) $tmpRes.= $schluessel.": ".$wert."\n";
		$payment->sLog("Response SetExpressCheckout", $tmpRes, false);
	}

   $ack = strtoupper($resArray["ACK"]);
	
   if($ack=="SUCCESS"){
		/*Redirect to paypal.com here*/
		$token = urldecode($resArray["TOKEN"]);
		$payPalURL = PAYPAL_URL.$token;		
		$payment->sSYSTEM->_SESSION['payPalURL'] = PAYPAL_URL.$token;
		
		header("Location: ".$payPalURL);
		exit;
	} else  {
		/*Redirecting to APIError to display errors.*/ 
		$location = $url.$payment->config["sBASEFILE"].'/sViewport,paypalexpressAPIError';
		header("Location: $location");
		exit;				
	}

?>