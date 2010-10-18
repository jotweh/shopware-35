<?php
/*
ClickandBuy-Schnittstelle
Version 1.2
(c)2009, PayIntelligent 
*/

$path = "../";	// Rel. Pfad zur Payment-Klasse
include("clickandbuy.class.php");	// Standard-Payment-Klasse laden

/*
Neue Instanz der Klasse erzeugen.
Parameter - 1 : Hier koennen Sie eine Mailadresse angeben, an die moegliche Debug-Meldungen geschickt werden
Parameter - 2 : Der relative Pfad zur Payment-Klasse
*/
$payment = new clickandbuyPayment("","../");										

/*Laedt alle verfuegaren User-Daten, diese stehen anschliessend im array payment->sUser bereit*/
$payment->initUser();

/*Enthaelt den Namen der Zahlungsart, die der Kunde aktuell gewaehlt hat*/
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

/*Pruefen ob AGBs akzeptiert wurden*/
if (empty($_REQUEST["sAGB"]) && empty($payment->config['sIGNOREAGB'])){
	header("Location: ".dirname($_SERVER['PHP_SELF']).'/form.php?sAGBError=1');
	exit;
}

/*Setze ClickandBuy Link*/
$cabLink =  $payment->cabLink;

if(empty($cabLink)) {
	die('Config missing');
}

/*Ermittelt den aktuellen Bestellwert*/
$value = $payment->getAmount();
$cab_price = $payment->formatAmountCent($value);
$cb_currency = $payment->sSYSTEM->sCurrency["currency"] ? $payment->sSYSTEM->sCurrency["currency"] : "EUR";

/*ClickandBuy Sonderbehandlung fuer Preise unter 1EUR bsp. Preis von 0,90 EUR entspricht price=090*/
if($cab_price < 100) {	
	if($cab_price < 10)  $cab_price = "00".$cab_price;
	else $cab_price = "0".$cab_price;
}

/*Setze Land, Language ISO Code & Info Text zur Bestellung*/
$nation		  = $userData["additional"]["country"]["countryiso"];

if ($payment->sSYSTEM->sLanguage == 1) $sLanguageIso = "de";
else  $sLanguageIso = "en";

if ($sLanguageIso== "de") $cb_content_name = "Ihre+Bestellung";	
else $cb_content_name = "Your+Order";

/*Setze Ausnahme fuer iso Country UK statt GB*/
if ($nation == "GB") $nation = "UK";

$location_param = "&cb_billing_Nation=".$nation."&lang=".$sLanguageIso;

/*Abfrage des Warenkorbs*/
$basket = $payment->getBasket();

/*Falls Warenkorb leer oder Bestellwert = 0 => Abbruch der Zahlung*/
if (!$basket["content"][0] || $value<=0){
	echo $payment->sSYSTEM->sCONFIG['sSnippets']['sClickAndBuyOrderAlreadySent'];
	exit;
}

/*Eindeutige Bestell-ID generieren*/
$bookingId = substr(md5(uniqid(rand())),0,10);  

/*Kundendatenuebergabe nur mit Zustimmung des Haendlers Daten setzen*/
$customerData =  $payment->customerData;
$user_param   =	 "";
 
if ($customerData == 1) {
	
	$salutation	 = $userData["billingaddress"]["salutation"];
	$firstName 	 = $userData["billingaddress"]["firstname"];
	$lastName	 = $userData["billingaddress"]["lastname"];
	$street	 	 = $userData["billingaddress"]["street"];
	$houseNumber = $userData["billingaddress"]["streetnumber"];
	$zip	 	 = $userData["billingaddress"]["zipcode"];
	$city	 	 = $userData["billingaddress"]["city"];
	
	$cb_shipping_FirstName = utf8_encode($userData["shippingaddress"]["firstname"]);
	$cb_shipping_LastName = utf8_encode($userData["shippingaddress"]["lastname"]);
	$cb_shipping_Street = utf8_encode($userData["shippingaddress"]["street"]);
	$cb_shipping_HouseNumber = $userData["shippingaddress"]["streetnumber"];
	$cb_shipping_ZIP = $userData["shippingaddress"]["zipcode"];
	$cb_shipping_City = utf8_encode($userData["shippingaddress"]["city"]);
	$cb_shipping_Nation = $userData["additional"]["countryShipping"]["countryiso"];
	
	$cb_shipping = 	"&cb_shipping_FirstName=".$cb_shipping_FirstName."&cb_shipping_LastName=".$cb_shipping_LastName."&cb_shipping_Street=".$cb_shipping_Street."&cb_shipping_HouseNumber=".$cb_shipping_HouseNumber."&cb_shipping_ZIP=".$cb_shipping_ZIP."&cb_shipping_City=".$cb_shipping_City."&cb_shipping_Nation=".$cb_shipping_Nation;
	
	/*Setze gender M/F*/
	if ($salutation == "mr") $gender = "M"; 
	else $gender = "F";
	
	$user_param = "&Gender=".$gender."&cb_billing_FirstName=".$firstName."&cb_billing_LastName=".$lastName."&cb_billing_Street=".$street."&cb_billing_HouseNumber=".$houseNumber."&cb_billing_ZIP=".$zip."&cb_billing_City=".$city.$cb_shipping;	
} 

$dispatchID = $payment->sSYSTEM->_SESSION["sDispatch"];	// SHOPWARE 2.0.4
  
$custom = session_id()."|".$bookingId."|".$payment->sSYSTEM->sLanguage."|".$payment->sSYSTEM->sCurrency["id"]."|".$payment->sSYSTEM->_SESSION["sSubShop"]["id"]."|".$payment->sSYSTEM->_SESSION["sDispatch"];

/*Weiterleitungs-URL zusammenstellen*/
$param = "?price=".$cab_price."&cb_currency=".$cb_currency."&cb_content_name=".$cb_content_name."&externalBDRID=".$bookingId."&custom=".$custom;
$url = $cabLink.$param.$location_param.$user_param;

/*
Debug E-Mail versenden
mail("","DUMP1",$url);
*/
/*Weiterleitung zur Bezahlung mit ClickandBuy*/
header ("Location: ".$url);
?>