<?php
// Shopware 3.0
// English language file

/*
saferpay/form.php
*/
$sLang["saferpay"]["terms"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayTerms'];
$sLang["saferpay"]["info"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayInfo'];
$sLang["saferpay"]["continue"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayContinue'];
$sLang["saferpay"]["paymentMeanError"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayPaymentMeanError'];
$sLang["saferpay"]["testsystemError"] = $payment->sSYSTEM->sCONFIG['sSnippets']['sSaferpayTestsystemError'];

/*
saferpay/doPayment.php
*/
$sLang["saferpay"]["curlNotInstalled"] = "PHP-CURL is not installed or activated on your system!";
$sLang["saferpay"]["saferpayDescription"] = "Your Order";
$sLang["saferpay"]["order"] = "Your basket is empty";
$sLang["saferpay"]["back"] = "back";
$sLang["saferpay"]["curlNotWorking"] = "PHP-CURL is not working correctly for outgoing SSL-calls on your server";

/*
saferpay/fail.php
*/
$sLang["saferpay"]["fail"] = "Your Saferpay authorization was not successful";
$sLang["saferpay"]["click"] = "Click";
$sLang["saferpay"]["here"] = "here";
$sLang["saferpay"]["checkout"] = "to checkout";

/*
saferpay/doCheckoutPayment.php
*/

$sLang["saferpay"]["sKeyFailed"] = "OderID / SessionID failed, possible manipulation";
$sLang["saferpay"]["SaferpayID"] = "SaferpayID is already booked";
$sLang["saferpay"]["confirmationFailed"] = "Confirmation failed";
$sLang["saferpay"]["wrongAccountID"] = "ACCOUNTID wrong, possible manipulation";
$sLang["saferpay"]["wrongAmount"] = "AMOUNT wrong, possible manipulation";
$sLang["saferpay"]["wrongCurrency"] = "CURRENCY wrong, possible manipulation";
$sLang["saferpay"]["wrongOrder"] = "ORDER ID wrong, possible manipulation";
$sLang["saferpay"]["captureFailed"] = "Confirmation OK - Capture failed";

?>