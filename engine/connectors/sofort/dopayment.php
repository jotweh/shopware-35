<?php
$path = "../";															// Rel. Pfad zur Payment-Klasse
include("sofort.class.php");											// Standard-Payment-Klasse laden
/*
Neue Instanz der Klasse erzeugen.
Parameter - 1 : Hier können Sie eine Mailadresse angeben, an die mögliche Debug-Meldungen geschickt werden
Parameter - 2 : Der relative Pfad zur Payment-Klasse
*/
$payment = new sofortPayment("/dev/null","../");									

/*
Lädt alle verfügaren User-Daten, diese stehen anschließend im array payment->sUser bereit
*/
$payment->initUser();

/*
Enthält den Namen der Zahlungsart, die der Kunde aktuell gewählt hat
*/
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

$back = 'http://'.$payment->config['sBASEPATH'].'/'.$payment->config['sBASEFILE'].'/sViewport,sale';;

// Prüfen ob AGBs akzeptiert wurden
if (empty($_REQUEST["sAGB"]) && empty($payment->config['sIGNOREAGB'])){
	header("Location: ".dirname($_SERVER['PHP_SELF']).'/form.php?sAGBError=1');
	exit;
}

if(!empty($payment->sSYSTEM->sCurrency["currency"]) && !in_array($payment->sSYSTEM->sCurrency["currency"], array('EUR', 'CHF', 'GBP'))) {
	echo 'Ihre ausgewählte Währung wird nicht mit dieser Zahlungsart unterstützt.<br />Bitte wechseln Sie daher ihre Währung.<br /><a target="_top" href="'.$back.'">zurück</a>';
	exit;
}

/*
Ermittelt den aktuellen Bestellwert
*/
$value = $payment->getAmount();

/*
Abfrage des Warenkorbs
*/

$basket = $payment->getBasket();

/*
Falls Warenkorb leer oder Bestellwert = 0 => Abbruch der Zahlung
*/
if (!$basket["content"][0] || $value<=0){
	echo 'Die Bestellung wurde bereits abgeschickt<br /><a target="_top" href="'.$back.'">zurück</a>';
	exit;
}

/*
Bestellwert formatieren
*/
$value = $betrag = number_format($value, 2, '.','');

/*
Projektdaten setzen
*/
$user_id =  $payment->user_id;
$project_id = $payment->projectID;
$securitykey = $payment->secretKey;

$bookingId = substr(md5(uniqid(rand())),0,10);  

/*
Überweisungsbetreff generieren
*/
$userData = $payment->sUser;

$zweck1 = $userData["billingaddress"]["customernumber"]."-".$userData["billingaddress"]["firstname"]." ".$userData["billingaddress"]["lastname"];
$zweck1 = preg_replace("/[^a-zA-Z0-9 -]/","",$zweck1);
$zweck2 = $bookingId;
$language =  $payment->sSYSTEM->sLanguage;
$currency =  $payment->sSYSTEM->sCurrency["currency"];
if (empty($currency)) $currency = "EUR";
$subshop =  $payment->sSYSTEM->_SESSION["sSubShop"]["id"];
$dispatchID = $payment->sSYSTEM->_SESSION["sDispatch"];

$custom = session_id()."|".$bookingId."|".$payment->sSYSTEM->sLanguage."|".$payment->sSYSTEM->sCurrency["id"]."|".$payment->sSYSTEM->_SESSION["sSubShop"]["id"]."|".$dispatchID;

$shop_url = $payment->sSYSTEM->sCONFIG['sBASEPATH'];

/*
Security-Hash generieren
*/
$data = array(
	$user_id, // user_id
	$project_id, // project_id
	'', // sender_holder
	'', // sender_account_number
	'', // sender_bank_code
	'', // sender_country_id"
	$value, // amount
	$currency, // currency_id
	$zweck1, // reason_1
	$zweck2, // reason_2
	session_id(), // user_variable_0
	$bookingId, // user_variable_1
	$custom, // user_variable_2
	$shop_url, // user_variable_3
	'', // user_variable_4
	'', // user_variable_5
	$securitykey // project_password
);

$data_implode = implode('|', $data);
$hash = md5($data_implode);

/*
Weiterleitungs-URL generieren
*/

$url = "?user_id=".$user_id."&project_id=".$project_id."&amount=".$value."&currency_id=".$currency."&reason_1=".$zweck1."&reason_2=".$zweck2."&user_variable_0=".session_id()."&user_variable_1=$bookingId&user_variable_2={$custom}&hash=".$hash;
$url .= '&user_variable_3='.urlencode($shop_url);
$url .= "&interface_version=shopware_v305";
header ("Location: https://www.sofortueberweisung.de/payment/start".$url);

?>