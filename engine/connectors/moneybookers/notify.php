<?php
$debug = "/dev/null"; // Change to your mail-adress to receive debug-information
$path = "../";

include("moneybookers.class.php");

$payment = new moneybookersPayment($debug,"../",false);
$payment->catchErrors();

// Übergebene Shop Params auswerten
$custom_org = urldecode($_REQUEST['param_custom']);
$custom = explode("-",$custom_org);	//Custom Array

$currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";
$skey     = $custom[6];
$sVerify  = md5("deadbeef".$custom[0].$custom[1].$custom[7]."F3e5b9C6");

/*Init Shopware param*/
$_REQUEST["trans_id"] = $custom[1];
#$_REQUEST["coreID"] 	= $custom[0];	//CoreID / Session
$_REQUEST["uniqueID"] 	= $custom[1];	//BookingID
$_REQUEST["sUniqueID"] 	= $custom[1];	//BookingID
$_REQUEST["sLanguage"]	= $custom[2];	//Language
$_REQUEST["sCurrency"] 	= $currency;	//Currency
$_REQUEST["sSubShop"] 	= intval($custom[4]);	//Subshop-ID
$_REQUEST["dispatchID"] = intval($custom[5]);	//Dispatch
$_REQUEST["param_dispatchID"] = intval($custom[5]);	//Dispatch

$merchantId     = $_POST['merchant_id'];
$transactionId  = $_POST['transaction_id'];
$mbAmount       = $_POST['mb_amount'];
$amount         = $_POST['amount'];
$mbCurrency     = $_POST['mb_currency'];
$status         = $_POST['status'];
$checksum = $payment->getCheckSum($merchantId, $transactionId, $mbAmount, $mbCurrency, $status);
#echo $checksum.' = '.$_POST['md5sig'];
#mail('webmaster@web-dezign.de', 'Shopware Moneybookers Notify Mail 1', $checksum.' = '.$_POST['md5sig']);
if (/*$_POST['md5sig'] == $checksum && */ $skey == $sVerify){
  if ($status == 2){
    $payment->initUser();
    $payment->initPayment();
    $_REQUEST["transaction"] = $transactionId;
    $ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",sMONEYBOOKERS_STATUS_ID);
  }
  $payment->setStatus($transactionId, $status, $amount);
  echo 'OK';
} else {
  echo 'FALSE';
}
