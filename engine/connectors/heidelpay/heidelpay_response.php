<?php
$debug = "/dev/null"; // Change to your mail-adress to receive debug-information
$path = "../";

include("heidelpay.class.php");

$payment = new heidelpayPayment($debug,"../",false);
$queryString = '?order_id='.$_POST['IDENTIFICATION_TRANSACTIONID'].'&coreID='.$_GET['coreID'].'&uid='.$_POST['IDENTIFICATION_UNIQUEID'].'&shortid='.$_POST['IDENTIFICATION_SHORTID'].'&custom='.$_REQUEST['custom'];

if ($payment->mailDebug) mail($payment->debugEmail, 'heidelpay_response.php', print_r($_POST,1));

$returnvalue=$_POST['PROCESSING_RESULT'];
if ($returnvalue){
  if (strstr($returnvalue,"ACK")) {
    print $payment->urlOK.$queryString;
  } else if ($_POST['FRONTEND_REQUEST_CANCELLED'] == 'true'){
    print $payment->urlCancel;
  } else {
    print $payment->urlFail;
  }
} else {
  echo 'FAIL';
}
?>