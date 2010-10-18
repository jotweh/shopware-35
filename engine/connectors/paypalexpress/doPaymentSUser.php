<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

doPaymentSUser.php

**********************************************************/

$path = "../";	 
include("paypalexpress.class.php");	 

/*Enter your E-Mail for debugging*/
$debug = "";  
$payment = new paypalexpressPayment($debug,"../");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "0";

$serverName = $payment->sSYSTEM->sCONFIG["sBASEPATH"];
 if (empty($payment->config["sUSESSL"])){
   	$url= "http://".$serverName."/";
   }else {
   	$url= "https://".$serverName."/";
   }

/*Check user sign-in*/
if (!$payment->sSYSTEM->sMODULES['sAdmin']->sCheckUser()){
	/*Redirecting to Viewport sale.*/ 
	$location = $url.$payment->config["sBASEFILE"].'/sViewport,sale';
	header("Location: $location");
	exit();
}

/*Check Terms*/
if ($payment->config['sIGNOREAGB']!="1" && $_GET['type']!='express') {

	if (!$_POST["sAGB"]){	
		$payment->sSYSTEM->_SESSION['sAGB'] = 'error';	
		/*Redirecting to Viewport sale.*/ 
		$location = $url.$payment->config["sBASEFILE"].'/sViewport,sale';
		header("Location: $location");
		exit();
	}	
}

/*Init user data*/
$payment->initUser();
$userData = $payment->sUser;

/*Include PayPal*/
require_once 'CallerService.php';
require_once 'constants.php';


//********************************************************************************************************

/*Generate unique Transaction-ID*/
$bookingId = substr(md5(uniqid(rand(),true)),0,10);  
$payment->sSYSTEM->_SESSION['trans_id'] = $bookingId;

$params = array();

$amount = $payment->getAmount();	
$shipping = $payment->getShippingCosts();

$amount_net = $payment->getAmountNet();	
$shipping_net = $payment->getShippingCostsNet();
	
$item_tax_amount = ($amount-$amount_net)-($shipping-$shipping_net);
$item_amount = $amount_net-$shipping_net;

$params['AMT'] = number_format($amount,2,'.',',');

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

$params['SHIPTONAME'] = $userData['shippingaddress']['firstname'].' '.$userData['shippingaddress']['lastname'];
$params['SHIPTOSTREET'] = $userData['shippingaddress']['street'].' '.$userData['shippingaddress']['streetnumber'];
$params['SHIPTOZIP'] = $userData['shippingaddress']['zipcode'];
$params['SHIPTOCITY'] = $userData['shippingaddress']['city'];
$params['SHIPTOCOUNTRYCODE'] = $userData['additional']['countryShipping']['countryiso'];
$params['SHIPTOCOUNTRYNAME'] = $userData['additional']['countryShipping']['countryname'];
$params['LOCALECODE'] = Shopware()->Locale()->getRegion();
$params['SHIPTOPHONENUM'] =  $userData['billingaddress']['phone'];
$params['ADDROVERRIDE'] = 1;

foreach ($params as $key => $param) {
	$params[$key] = utf8_encode(htmlspecialchars_decode(strip_tags($param)));
}

$params = '&'.http_build_query($params, '', '&');


$custom = session_id()."|".$bookingId."|".$payment->sSYSTEM->sLanguage."|".$payment->sSYSTEM->sCurrency["id"]."|".$payment->sSYSTEM->_SESSION["sSubShop"]["id"]."|".$payment->sSYSTEM->_SESSION["sDispatch"];
$payment->sSYSTEM->_SESSION['customArray'] = $custom;

//*************************************************************************************
/* The servername and serverport tells PayPal where the buyer
   should be directed back to after authorizing payment.
   In this case, its the local webserver that is running this script
   Using the servername and serverport, the return URL is the first
   portion of the URL that buyers will return to after authorizing payment
   */
   		
	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";

	$currencyCodeType=$currency;
	$paymentType= PAYMENT_TYPE;

	/*Check basket*/
	$basket = $payment->getBasket();

	/*If basket is empty exit*/
	if (!$basket["content"][0] || $amount<=0){
		echo $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressOrderAlreadySent'];
		exit();
	}

 /* The returnURL is the location where buyers return when a
	payment has been succesfully authorized.
	The cancelURL is the location buyers are sent to when they hit the
	cancel button during authorization of payment during the PayPal flow
	*/

	if($_GET['type']=='express') {				
		$useraction = "";			
		$returnURL = $url.'engine/connectors/paypalexpress/GetExpressCheckoutDetails.php?coreId='.session_id().'&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&paymentAmount='.$amount;
	} else {
		$useraction = "&useraction=commit";			
		$returnURL = $url.'engine/connectors/paypalexpress/GetExpressCheckoutDetails.php?coreId='.session_id().'&currencyCodeType='.$currencyCodeType.'&paymentType='.$paymentType.'&paymentAmount='.$amount.'&useraction=commit';
	}
	
    $cancelURL = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sRefererAllowed,1/sCoreId,'.session_id().'/booking,'.$bookingId.'/?paymentType='.$paymentType;    
    $giropaySuccessURL = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.session_id().'/sUniqueID,'.$bookingId.'/'; 
    $giropayCancelURL = $cancelURL;
    $giropayTXNPendingURL = $url.$payment->config["sBASEFILE"].'/sViewport,paypalexpressTXNPending';
         
   	$returnURL =urlencode($returnURL);
   	$cancelURL =urlencode($cancelURL);
  	$giropaySuccessURL =urlencode($giropaySuccessURL);
   	$giropayCancelURL =urlencode($giropayCancelURL);
   	$giropayTXNPendingURL =urlencode($giropayTXNPendingURL);

 /* Construct the parameter string that describes the PayPal payment
	the varialbes were set in the web form, and the resulting string
	is stored in $nvpstr
	*/
  
   $nvpstr="&PAYMENTACTION=".$paymentType.$params."&RETURNURL=".$returnURL."&CANCELURL=".$cancelURL ."&GIROPAYSUCCESSURL=".$giropaySuccessURL."&GIROPAYCANCELURL=".$giropayCancelURL."&BANKTXNPENDINGURL=".$giropayTXNPendingURL."&CURRENCYCODE=".$currencyCodeType;

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

		$location = PAYPAL_URL.$token.$useraction;
		$payment->sSYSTEM->_SESSION['payPalURL'] = PAYPAL_URL.$token;

		//header("Location: ".$location);

	} else  {
		/*Redirecting to APIError.php to display errors.*/
		$location = $url.$payment->config["sBASEFILE"].'/sViewport,paypalexpressAPIError';
		//header("Location: $location");
	}
?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
 <head>
  <title>Sie werden weitergeleitet auf $location</title>
<!--  
  <meta http-equiv="Refresh" content="1; URL=<?php echo $location;?>">
-->
  <script type="text/javascript">top.location.replace('<?php echo $location;?>');</script>
 </head>
 <body>Klicken Sie <a href="<?php echo $location;?>" target="_top">Hier</a> falls Sie nicht weiter geleitet werden.</body>
</html>