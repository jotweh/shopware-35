<?php
# http://www.shopware.vm/engine/connectors/hanseatic/poll.php
# Max 6 mal am Tag ausführen also ca. alle 4 Std.

$debug = ""; // Change to your mail-adress to receive debug-information

if (!$_SERVER['DOCUMENT_ROOT']){
	
	if (preg_match("/engine/",$_SERVER["PHP_SELF"])){
		$_SERVER['DOCUMENT_ROOT'] = preg_replace("/(.*)\/engine\/(.*)/","\\1",$_SERVER["PHP_SELF"]);
	}else {
		if (preg_match("/engine/",$_SERVER["PWD"])){
			$_SERVER['DOCUMENT_ROOT'] = preg_replace("/(.*)\/engine\/(.*)/","\\1",$_SERVER["PWD"]);
		}
		if (empty($_SERVER['DOCUMENT_ROOT'])){
			die ("Could not fetch absolute path");
		}
	}
}

$prepareTimeStart = array_sum(explode(chr(32), microtime()));

if (!is_file($_SERVER['DOCUMENT_ROOT']."/config.php")){
	$_SERVER['DOCUMENT_ROOT'] .= preg_replace("/(.*)\/engine\/(.*)/","\\1",$_SERVER["PHP_SELF"]);
}

$path = $_SERVER['DOCUMENT_ROOT']."/engine/connectors/";


include("hanseatic.class.php");

$payment = new hanseaticPayment($debug,"../",false);
$payment->catchErrors();

$pollURL = $payment->getPollURL();
#echo $pollURL;
$pollData = $payment->doCurlRequest($pollURL, array());
#echo '<pre>'.htmlspecialchars(print_r($pollData, 1)).'</pre>';
$pollData = $payment->decodeXML($pollData);
if (!is_array($pollData)) $pollData = array();
#echo '<pre>'.print_r($pollData, 1).'</pre>';

foreach($pollData AS $k => $v){
  $reference = $v['reference'];
  $status = $v['status'];
  $amount = $v['amount'];
  $payment->setStatus($reference, $status, $amount);
}
?>