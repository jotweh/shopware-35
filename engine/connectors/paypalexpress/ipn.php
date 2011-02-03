<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

ipn.php

**********************************************************/

$path = "../";	
include("paypalexpress.class.php");	

/*Enter your E-Mail for debugging*/
$debug = ""; 
$payment = new paypalexpressPayment($debug,"../");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "0";

require_once ("constants.php");

$config_path = "../../../";
include($config_path."config.php"); 	

/*Read the post from PayPal system and add 'cmd'*/
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}

/*Post back to PayPal system to validate*/
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
//$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
$fp = fsockopen ('ssl://'.PAYPAL_IPNURL, 443, $errno, $errstr, 30);

/*Assign posted variables to local variables*/
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$payer_id = $_POST['payer_id'];



if (!$fp) {
/*HTTP ERROR*/
} else {

/*Log PayPal Request*/
if ($paypalLogging == "1") {
	$payment->sLog("Request IPN", $req, false);
}

fputs ($fp, $header . $req);
while (!feof($fp)) {
$res = fgets ($fp, 1024);

/*Log PayPal Response*/
if ($paypalLogging == "1") {
	$payment->sLog("Response IPN", $res, false);
}

if (strcmp ($res, "VERIFIED") == 0) {
	$sql = "SELECT * FROM paypal_orders WHERE transactionId = ?";
	$result = $payment->sDB_CONNECTION->GetRow($sql, array($txn_id));
	
		/*EventID is not in DB, insert!*/
		if ($result['paypalPaymentStatus'] != $payment_status) { 
			$ipnStatus.= " payment_status is different";

			$status= 21;
			if ($payment_status=='Completed') $status= 12;
			if ($payment_status=='Pending') $status= 18;
			if ($payment_status=='Created') $status= 17;
			if ($payment_status=='Refunded') $status= 20;
			if ($payment_status=='Reversed') $status= 8;
			if ($payment_status=='Cancelled_Reversal') $status= 12;

			/* check the payment_status is Completed
			   check that txn_id has not been previously processed
			   check that receiver_email is your Primary PayPal email
			   check that payment_amount/payment_currency are correct
			   process payment 
			   */

			if ($payment_status == 'Completed') { 
		
				if ($result['payerId'] == $payer_id && $result['price'] == $payment_amount && $result['currency'] == $payment_currency) {

					/*Update paypal_orders Status*/
					$sql  = "UPDATE paypal_orders SET paymentStatus = ?, booked = 1, ipn = ?, dateTime = ? WHERE stransId = ? LIMIT 1";
					$payment->sDB_CONNECTION->Execute($sql, array($payment_status, $req, date("Y-m-d H:i:s"), $result['stransId']));			
						
					/*Update s_order Status*/
					$sql  = "UPDATE s_order SET cleared=? WHERE transactionID=? LIMIT 1";
					$payment->sDB_CONNECTION->Execute($sql, array($status, $result['stransId']));
				} 
			} else {

				/*Update paypal_orders Status*/
				$sql  = "UPDATE paypal_orders SET paymentStatus = ?, ipn = ?, dateTime =? WHERE stransId = ? LIMIT 1";
				$payment->sDB_CONNECTION->Execute($sql, array($payment_status, $req, date("Y-m-d H:i:s"), $result['stransId']));
				
				if ($payment_status != 'Refunded') {					
					/*Update s_order Status*/
					$sql  = "UPDATE s_order SET cleared=? WHERE transactionID = ? LIMIT 1";
					$payment->sDB_CONNECTION->Execute($sql, array($status, $result['stransId']));
				}
			}					
		} 
}
else if (strcmp ($res, "INVALID") == 0) {
/*Log for manual investigation*/
}
}
fclose ($fp);
}

?>