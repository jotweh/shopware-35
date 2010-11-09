<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

DoExpressCheckoutPayment.php

This functionality is called to complete the payment with
PayPal and display the result to the buyer.

The code constructs and sends the DoExpressCheckoutPayment
request string to the PayPal server.

**********************************************************/

$path = "../";	
include("paypalexpress.class.php");	

/*Enter your E-Mail for debugging*/
$debug = ""; 
$payment = new paypalexpressPayment($debug,"../");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "0";

require_once 'constants.php';


/*Init user data*/
$payment->initUser();
$userData = $payment->sUser;


require_once 'CallerService.php';

    $serverName = $payment->sSYSTEM->sCONFIG["sBASEPATH"];
	 if (empty($payment->config["sUSESSL"])){
	   	$url= "http://".$serverName."/";
	   }else {
	   	$url= "https://".$serverName."/";
	   }
 
	/*Check Terms and conditions*/
	if ($payment->config['sIGNOREAGB']!="1" && $_GET['useraction']!='commit') {
	
		if (!$_POST["sAGB"]){	
			$payment->sSYSTEM->_SESSION['sAGB'] = 'error';	
			/*Redirecting to Viewport sale.*/ 
			$location = $url.$payment->config["sBASEFILE"].'/sViewport,sale';
			header("Location: $location");
			exit();
		}	
	}
 
	$transId = $payment->sSYSTEM->_SESSION['trans_id'];
	
	
	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
	
	/*Initialize Shopware parameters*/
	$_REQUEST["trans_id"] = $transId;
	$_REQUEST["token"] = urlencode( $payment->sSYSTEM->_SESSION['token']);
	$custom = explode("|",$payment->sSYSTEM->_SESSION["customArray"]);
	$_REQUEST["coreID"] = $custom[0];
	$_REQUEST["uniqueID"] = $custom[1];
	$_REQUEST["sLanguage"] = $custom[2];	
	$_REQUEST["sCurrency"] = $custom[3]; 	
	$_REQUEST["sCurrency"] = $currency; 
	$_REQUEST["sSubShop"] = intval($custom[4]);		
	$_REQUEST["dispatchID"] = intval($custom[5]);	

	
	/* Gather the information to make the final call to
	   finalize the PayPal payment.  The variable nvpstr
	   holds the name value pairs
	   */
	$token =urlencode($payment->sSYSTEM->_SESSION['token']);

	/*Set basic parameters for paypal*/
	$paymentType = PAYMENT_TYPE;
	
	$currCodeType = urlencode($currency);
	$payerID = urlencode($payment->sSYSTEM->_SESSION['payer_id']);
	$serverName = urlencode($payment->sSYSTEM->_SESSION['SERVER_NAME']);
	

	$params = array();
	
	$amount = $payment->getAmount();	
	$shipping = $payment->getShippingCosts();
	
	$amount_net = $payment->getAmountNet();	
	$shipping_net = $payment->getShippingCostsNet();
		
	$item_tax_amount = ($amount-$amount_net)-($shipping-$shipping_net);
	$item_amount = $amount_net-$shipping_net;
	
	$params['AMT'] = number_format($amount,2,'.',',');
/*
	if(!empty($item_tax_amount)) {
		$params['SHIPPINGAMT'] = number_format($shipping,2,'.',',');
		$params['HANDLINGAMT'] = 0;
		
		$params['TAXAMT'] = number_format($item_tax_amount,2,'.',',');
		$params['ITEMAMT'] = number_format($item_amount,2,'.',',');
		
		$basket = $payment->getBasket();
		foreach ($basket['content'] as $key => $item) {
			$item['tax'] = str_replace(',', '.', $item['tax']);
			$item['amount'] = str_replace(',', '.', $item['amount']);
			
			$params['L_NAME'.$key] = $item['articlename'];
			$params['L_NUMBER'.$key] = $item['ordernumber'];
			$params['L_AMT'.$key] = number_format($item['amount']-$item['tax'],2,'.',',');
			$params['L_TAXAMT'.$key] = number_format($item['tax'],2,'.',',');
		}
	}
*/
	$params['SHIPTONAME'] = $userData['shippingaddress']['firstname'].' '.$userData['shippingaddress']['lastname'];
	$params['SHIPTOSTREET'] = $userData['shippingaddress']['street'].' '.$userData['shippingaddress']['streetnumber'];
	$params['SHIPTOZIP'] = $userData['shippingaddress']['zipcode'];
	$params['SHIPTOCITY'] = $userData['shippingaddress']['city'];
	$params['SHIPTOCOUNTRYCODE'] = $userData['additional']['countryShipping']['countryiso'];
	$params['SHIPTOCOUNTRYNAME'] = $userData['additional']['countryShipping']['countryname'];
	
	foreach ($params as $key => $param) {
		$params[$key] = utf8_encode(htmlspecialchars_decode(strip_tags($param)));
	}
	
	$params = '&'.http_build_query($params, '', '&');
	
	/*URL for receiving Instant Payment Notification (IPN) about this transaction.*/
	$notifyURL = $url.'engine/connectors/paypalexpress/ipn.php';
	$notifyURL =urlencode($notifyURL);
	
	/*Shopware invoice or tracking number*/
	$invnum = $transId;

	/*Shopware UserID*/
	$shopwareUID= $userData["billingaddress"]["customernumber"];; 	
	
	$param = "&BUTTONSOURCE=Shopware_Cart_EC_DE&DESC=sOrderID-".$transId."&INVNUM=".$invnum."&NOTIFYURL=".$notifyURL.$params;
	
	$nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTACTION='.$paymentType.'&CURRENCYCODE='.$currCodeType.$param;
	
	 /* Make the call to PayPal to finalize payment
	    If an error occured, show the resulting errors
	    */
	    
	/*Log PayPal Request*/
	if ($paypalLogging == "1") {
		$payment->sLog("Request DoExpressCheckoutPayment", $nvpstr, false);
	}
	    
	$resArray=hash_call("DoExpressCheckoutPayment",$nvpstr);

	/*Log PayPal Response*/
	if ($paypalLogging == "1") {
		foreach($resArray as $schluessel => $wert) $tmpRes.= $schluessel.": ".$wert."\n";
		$payment->sLog("Response DoExpressCheckoutPayment", $tmpRes, false);
	}	
	
	/* Display the API response back to the browser.
	   If the response from PayPal was a success, display the response parameters'
	   If the response was an error, display the errors received using APIError.php.
	   */
	$ack = strtoupper($resArray["ACK"]);
	$amt = strtoupper($resArray["AMT"]);	
	$paypalTransactionID = $resArray['TRANSACTIONID'];
	$redirectRequired = strtoupper($resArray['REDIRECTREQUIRED']);

	/* Status of the payment:
		Completed: The payment has been completed, and the funds have been added successfully to your account balance.
		Pending: The payment is pending. 
		*/
	$paypalPaymentStatus = $resArray['PAYMENTSTATUS'];
	
	if($ack!="SUCCESS"){
		$payment->sSYSTEM->_SESSION['reshash']=$resArray;
		$location = $url.$payment->config["sBASEFILE"].'/sViewport,paypalexpressAPIError';
		header("Location: $location");
	} else {

		$booked = 1;
		if ($paypalPaymentStatus == 'Completed') {
			$status = 12;		
		} elseif($paypalPaymentStatus == 'Pending' && $payment->paypalAuthorization == 1) {
			$status = 18;		
			$booked = 0;
		} else {
			$status = 17;		
			$booked = 0;
		}
			
		$authorization = $payment->paypalAuthorization;
		$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",$status);
	
		/*Insert into PayPal_orders*/
		$sql  = "INSERT INTO paypal_orders (suid,payerId,transactionId,paymentStatus,authorization,booked,price,refunded,currency,stransId, ipn, dateTime) ";
		$sql .= "VALUES ";
		$sql .= "('$shopwareUID','$payerID','$paypalTransactionID','$paypalPaymentStatus','$authorization','$booked','$amt', '0','$currCodeType','$transId', '', '".date("Y-m-d H:i:s")."')";
	
		$payment->sDB_CONNECTION->Execute($sql);	
		
		if ($redirectRequired == 'TRUE') {
			$success_url = PAYPAL_REDIRECTURL.$token;		
		} else {
			$success_url = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$custom[0].'/sUniqueID,'.$transId.'/';					
		}
			
		unset($payment->sSYSTEM->_SESSION['reshash']);
		unset($payment->sSYSTEM->_SESSION['token']);
		unset($payment->sSYSTEM->_SESSION['payer_id']);
		unset($payment->sSYSTEM->_SESSION['paymentAmount']);
		unset($payment->sSYSTEM->_SESSION['currCodeType']);
		unset($payment->sSYSTEM->_SESSION['paymentType']);
		unset($payment->sSYSTEM->_SESSION['trans_id']);
		unset($payment->sSYSTEM->_SESSION['customArray']);
		unset($payment->sSYSTEM->_SESSION['sAGB']);
		unset($payment->sSYSTEM->_SESSION['sAGB2']);
		
		header("Location: ".$success_url);

	}

?>