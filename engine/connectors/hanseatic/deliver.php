<?php
# http://www.shopware.vm/engine/connectors/hanseatic/deliver.php?oid=11248
# Muß bei jeder Auslieferung aufgerufen werden

$debug = "/dev/null"; // Change to your mail-adress to receive debug-information
$path = "../";

include("hanseatic.class.php");

$payment = new hanseaticPayment($debug,"../",false);
$payment->catchErrors();

$oID = (int)$_GET['oid'];
$deliverURL = $payment->getDeliverURL($oID);
#echo $deliverURL;
$res = $payment->doCurlRequest($deliverURL, array());
$res = $payment->decodeXML($res);
#print_r($res);
if ($res[1]['status'] == 1){
  echo 'OK';
} else {
  echo 'FAIL';
}
?>