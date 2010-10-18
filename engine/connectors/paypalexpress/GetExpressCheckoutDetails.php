<?php
/********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

GetExpressCheckoutDetails.php

This functionality is called after the buyer returns from
PayPal and has authorized the payment.

Displays the payer details returned by the
GetExpressCheckoutDetails response and calls
DoExpressCheckoutPayment.php to complete the payment
authorization.

Called by ReviewOrder.php.

Calls DoExpressCheckoutPayment.php and APIError.php.

********************************************************/
$path = "../";	
include("paypalexpress.class.php");	

$debug = ""; 
$payment = new paypalexpressPayment($debug,"../");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "0";

require_once 'CallerService.php';

	$payment->sSYSTEM->_SESSION['token']=$_GET['token'];
	$payment->sSYSTEM->_SESSION['payer_id'] = $_GET['PayerID'];
	
	$payment->sSYSTEM->_SESSION['paymentAmount']=$_GET['paymentAmount'];
	$payment->sSYSTEM->_SESSION['currCodeType']=$_GET['currencyCodeType'];
	$payment->sSYSTEM->_SESSION['paymentType']=$_GET['paymentType'];

	if ($payment->sSYSTEM->_SESSION['GuestUser'] != "1" && $payment->sSYSTEM->sMODULES['sAdmin']->sCheckUser()) {
		
		if($_GET['useraction'] == 'commit') {
			$url = 'http://'.$payment->config["sBASEPATH"].'/engine/connectors/paypalexpress/DoExpressCheckoutPayment.php?useraction=commit';		
		} else {
			$url = 'http://'.$payment->config["sBASEPATH"].'/engine/connectors/paypalexpress/DoExpressCheckoutPayment.php';		
		}
		
	} else {
		$payment->sSYSTEM->_SESSION['GuestUser'] = "1"; 
		$url = 'http://'.$payment->config["sBASEPATH"].'/'.$payment->config["sBASEFILE"].'/sViewport,paypalexpressGA';				
	}

	   $token =urlencode($_GET['token']);
	
	 /* Build a second API request to PayPal, using the token as the
		ID to get the details on the payment authorization
		*/
	   $nvpstr="&TOKEN=".$token;
	
	 /* Make the API call and store the results in an array.  If the
		call was a success, show the authorization details, and provide
		an action to complete the payment.  If failed, show the error
		*/
		
	/*Log PayPal Request*/
	if ($paypalLogging == "1") {
		$payment->sLog("Request GetExpressCheckoutDetails", $nvpstr, false);
	}
			
	$resArray=hash_call("GetExpressCheckoutDetails",$nvpstr);

	/*Log PayPal Response*/
	if ($paypalLogging == "1") {
		foreach($resArray as $schluessel => $wert) $tmpRes.= $schluessel.": ".$wert."\n";
		$payment->sLog("Response GetExpressCheckoutDetails", $tmpRes, false);
	}
		   
	   $payment->sSYSTEM->_SESSION['reshash']=$resArray;
	   $ack = strtoupper($resArray["ACK"]);

	/* Collect the necessary information to complete the
	   authorization for the PayPal payment
	   */
	
		$resArray=$payment->sSYSTEM->_SESSION['reshash'];
	
	
	/* Display the  API response back to the browser .
	   If the response from PayPal was a success, display the response parameters
	   */
	
	//Save paypal data!
	header("Location: ".$url.$param);

?>