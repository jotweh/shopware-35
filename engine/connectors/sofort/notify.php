<?php
$debug = ""; // Change to your mail-adress to receive debug-information
$path = "../";

include("sofort.class.php");

$_REQUEST['coreID'] = $_REQUEST['sCoreID'] = $_REQUEST['user_variable_0'];
$custom = explode("|",$_REQUEST["user_variable_2"]);
$_REQUEST["sLanguage"] = $custom[2];				// Language
$_REQUEST["sCurrency"] = $custom[3];				// Currency
if (empty($_REQUEST["sCurrency"])) $_REQUEST["sCurrency"] = "EUR";
$_REQUEST["sSubShop"] = intval($custom[4]);			// Subshop-ID
$_REQUEST["sDispatchID"] = intval($custom[5]);	// Dispatch

$payment = new sofortPayment($debug,"../",false);

$data = array(
	'transaction' => $_REQUEST['transaction'],
	'user_id' => $_REQUEST['user_id'],
	'project_id' => $_REQUEST['project_id'],
	'sender_holder' => $_REQUEST['sender_holder'],
	'sender_account_number' => $_REQUEST['sender_account_number'],
	'sender_bank_code' => $_REQUEST['sender_bank_code'],
	'sender_bank_name' => $_REQUEST['sender_bank_name'],
	'sender_bank_bic' => $_REQUEST['sender_bank_bic'],
	'sender_iban' => $_REQUEST['sender_iban'],
	'sender_country_id' => $_REQUEST['sender_country_id'],
	'recipient_holder' => $_REQUEST['recipient_holder'],
	'recipient_account_number' => $_REQUEST['recipient_account_number'],
	'recipient_bank_code' => $_REQUEST['recipient_bank_code'],
	'recipient_bank_name' => $_REQUEST['recipient_bank_name'],
	'recipient_bank_bic' => $_REQUEST['recipient_bank_bic'],
	'recipient_iban' => $_REQUEST['recipient_iban'],
	'recipient_country_id' => $_REQUEST['recipient_country_id'],
	'international_transaction' => $_REQUEST['international_transaction'],
	'amount' => $_REQUEST['amount'],
	'currency_id' => $_REQUEST['currency_id'],
	'reason_1' => $_REQUEST['reason_1'],
	'reason_2' => $_REQUEST['reason_2'],
	'security_criteria' => $_REQUEST['security_criteria'],
	'user_variable_0' => $_REQUEST['user_variable_0'],
	'user_variable_1' => $_REQUEST['user_variable_1'],
	'user_variable_2' => $_REQUEST['user_variable_2'],
	'user_variable_3' => $_REQUEST['user_variable_3'],
	'user_variable_4' => $_REQUEST['user_variable_4'],
	'user_variable_5' => $_REQUEST['user_variable_5'],
	'created' => $_REQUEST['created'],
	'notification_password' => $payment->sSYSTEM->sCONFIG["sNOTIFYKEY"]
);
$hash = md5(implode('|', $data));

if ($hash != $_REQUEST["hash"] && !empty($payment->sSYSTEM->sCONFIG["sNOTIFYKEY"])){
	$payment->throughError('Input check faild');
}

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

$factor = isset($payment->sSYSTEM->sCurrency['sCurrencyData'][$_REQUEST['sCurrency']]) ? $payment->sSYSTEM->sCurrency['sCurrencyData'][$_REQUEST['sCurrency']] : 1;

$_REQUEST["amount"] = number_format($_REQUEST["amount"] * $factor, 2, '.','');

if ($value!=$_REQUEST["amount"]){
	$payment->throughError("Überwiesener Betrag stimmt nicht mit Bestellwert überein: Bestellwert laut Shopware:$value\n Bestellwert laut Transaktion\n:{$_REQUEST["amount"]}\n",false);
	$status = 21;	
}
if (empty($status)) $status = 12;
$ordernumber = $payment->submitOrder("sale","../../core/class/viewports/","sViewportSale",$status);

?>