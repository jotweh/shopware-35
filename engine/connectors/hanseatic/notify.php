<?php
# http://www.shopware.vm/engine/connectors/hanseatic/notify.php?sha1=501f824365238b460de469b25f27c833412d3d26&reference=11244&status=0&amount=999.00

$debug = "/dev/null"; // Change to your mail-adress to receive debug-information
$path = "../";

include("hanseatic.class.php");

$payment = new hanseaticPayment($debug,"../",false);
$payment->catchErrors();

#echo '<pre>'.print_r($payment->config, 1).'</pre>';
#$payment->initUser();
#$userData = $payment->sUser;
#$payment->initPayment();

$checksum =  sha1(sHANSEATIC_PRESHAREDKEY.date('Ymd'));
#echo $checksum;
if ($_GET['sha1'] == $checksum){
  $reference = $_GET['reference'];
  $status = $_GET['status'];
  $amount = $_GET['amount'];
  $payment->setStatus($reference, $status, $amount);
  echo 'OK';
} else {
  echo 'FALSE';
}

?>