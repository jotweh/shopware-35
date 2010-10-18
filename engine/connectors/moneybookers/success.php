<?php
$path = "../";
include("moneybookers.class.php");
$payment = new moneybookersPayment("/dev/null",$path);									

# Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
$payment->initUser();

if (!empty($_REQUEST['custom'])){
	// Übergebene Shop Params auswerten
	$custom_org = urldecode($_REQUEST['custom']);
	$custom = explode("-",$custom_org);	//Custom Array

	$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
	$skey     = $custom[6];

	$sVerify  = md5("deadbeef".$custom[0].$custom[1].$custom[7]."F3e5b9C6");

	/*Init Shopware param*/
	$coreID = $custom[0];
	$_REQUEST["trans_id"] = $custom[1];
	#$_REQUEST["coreID"] 	= $custom[0];	//CoreID / Session
	$_REQUEST["uniqueID"] 	= $custom[1];	//BookingID
	$_REQUEST["sUniqueID"] 	= $custom[1];	//BookingID
	$_REQUEST["sLanguage"]	= $custom[2];	//Language
	$_REQUEST["sCurrency"] 	= $currency;	//Currency
	$_REQUEST["sSubShop"] 	= intval($custom[4]);	//Subshop-ID
	$_REQUEST["dispatchID"] = intval($custom[5]);	//Dispatch
	$_REQUEST["sComment"] = "";
} else {
	$skey = $sVerify = true;
}

# Prüfsumme checken
$transactionId = $_REQUEST["uniqueID"];
$check = $_GET["msid"];
$checkSum = $payment->getSecureSum(sMONEYBOOKERS_MERCHANTID, $transactionId);
#echo $check.' == '.$checkSum;
if (/*$check == $checkSum &&*/ $skey == $sVerify){

	// Prüfen ob die Bestellung nicht ggf. schon durch die Notify.php abgeschlossen wurde

	$orderId = $payment->getOrderIdByTransactionId($transactionId);

	if ($orderId <= 0){
		$_REQUEST['param_sCoreId'] = $_GET['coreID'];
		$payment->initPayment();
		$_REQUEST["transaction"] = $transactionId;
		$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sMONEYBOOKERS_STATUS_ID);
	}
	
	if (empty($payment->config["sUSESSL"])){
    	$url= "http://".$payment->config["sBASEPATH"]."/";
    } else {
    	$url= "https://".$payment->config["sBASEPATH"]."/";
    }

	$urlOK = $url.$payment->config["sBASEFILE"].'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$coreID.'/sUniqueID,'.$_REQUEST["sUniqueID"].'/';
	
	header('Location: '.$urlOK);
}