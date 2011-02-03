<?php
/*
iPayment-Schnittstelle
Version 1.2
(c)2008, Hamann-Media GmbH
*/

// Check if script-access is allowed
$allowed_ips= array(
    "212.227.34.218", 
    "212.227.34.219", 
    "212.227.34.220", 
    "195.20.224.139",
    "217.92.126.57"
);
if (! in_array($_SERVER["REMOTE_ADDR"], $allowed_ips)){
	exit();
}
  
$debug = ""; // Change to your mail-adress to receive debug-information
$path = "../";

if (!$_REQUEST["ret_trx_number"]) $_REQUEST["ret_trx_number"] = "TEST-BUCHUNG".rand(1,10000);

$custom = explode("|",$_REQUEST["custom"]);

$_REQUEST["coreID"] = $_REQUEST['sCoreID'] = $custom[0];

$_REQUEST["uniqueID"] = $custom[1];
$_REQUEST["sLanguage"] = $custom[2];				// Language
$_REQUEST["sCurrency"] = $custom[3];				// Currency
if (empty($_REQUEST["sCurrency"])) $_REQUEST["sCurrency"] = "EUR";
$_REQUEST["sSubShop"] = intval($custom[4]);			// Subshop-ID
$_REQUEST["dispatchID"] = intval($custom[5]);	// Dispatch

include("ipayment.class.php");
$payment = new ipaymentPayment($debug,"../",false);
$payment->catchErrors();
$payment->initUser();
$userData = $payment->sUser;
$payment->initPayment();

$value = $payment->getAmount();

/*
Abfrage des Warenkorbs
*/

$basket = $payment->getBasket();

/*
Bestellwert formatieren
*/
$value = number_format($value, 2, '.','');

if ($payment->formatAmountCent($value)!=$_REQUEST["trx_amount"]){
	$payment->throughError("Überwiesener Betrag stimmt nicht mit Bestellwert überein: Bestellwert laut Shopware:$value\n Bestellwert laut Transaktion\n:{$_REQUEST["trx_amount"]}\n",false);
	$status = 21;	
}
if (empty($status)) $status = 12;
$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",$status);
?>