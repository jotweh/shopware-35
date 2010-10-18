<?php
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

transactions.php

**********************************************************/
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
ini_set("display_errors",false);
$path = "../../../connectors/";	
include("../../../connectors/saferpay/saferpay.class.php");	
include("../../../connectors/saferpay/language_de.php");

/*Enter your E-Mail for debugging*/
$debug = ""; 
$payment = new saferpayPayment($debug,"../../../connectors/");										

$saferpayTestsystem = $payment->saferpayTestsystem;
$saferpayPassword = $payment->saferpayPassword;

/*Enable Saferpay logging = "1"*/
$saferpayLogging = "0";


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Reorder TreePanel</title>
<!-- Common Styles for the examples -->
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
</head>
<?php



if ($_POST["ordernr"] && $_POST["orderCommit"]){
		
	$error = array();
	$ordernumber = $_REQUEST["ordernr"];

	$queryOrder = mysql_query("SELECT transactionID FROM s_order WHERE ordernumber = {$ordernumber}");

	if (mysql_num_rows($queryOrder)>0){
  		$queryOrder = mysql_fetch_array($queryOrder);
  		$transactionID = $queryOrder['transactionID'];
	} else {
	  $error[] = "<script>parent.parent.Growl('".$sLang["paypalreserveorder"]["transactions_no_orders_found"]."')</script>";	
	}
	
	$queryOrder = mysql_query("SELECT * FROM saferpay_orders WHERE orders_id = '{$transactionID}'");

	if (mysql_num_rows($queryOrder)>0){

	  	$queryOrder = mysql_fetch_array($queryOrder);
		        
	    if (empty($error)) {

		    $saferpayAccountID = $queryOrder['saferpay_account_id'];
		    $saferpayToken = $queryOrder['saferpay_token'];
		    $saferpayID = $queryOrder['saferpay_id'];
		    $verification = $queryOrder['saferpay_complete_result'];
		    $currCodeType = $queryOrder['saferpay_currency'];
	
			// **************************************************
			// * Stop if verification not successful is not working
			// **************************************************
		
			if( strtoupper( substr( $verification, 0, 3 ) ) != "OK:" ) {						
				die("<strong>".$sLang["saferpay"]["confirmationFailed"]."</strong><br/>$verification");
			}
			
			
			// **************************************************
			// *
			// * Payment Capturing
			// *
			// **************************************************
			// * If you want directly to capture the amount of a successful authorization
			// * you could capture it as described directly after "back-from-authorization" 
			// * See the sample next steps:
			// **************************************************
		
			// **************************************************
			// * Parse ID and TOKEN out of $verification from Saferpay-Call VerifyPayConfirm
			// **************************************************
		
			$vpc = array();
			parse_str( substr( $verification, 3), $vpc ); 
		
			// **************************************************
			// * Constant: the hosting gateway URL to PayComplete 
			// **************************************************
		
			$saferpay_paycomplete_gateway = "https://www.saferpay.com/hosting/PayComplete.asp";
		
		
			// **************************************************
			// * Mandatory attributes
			// **************************************************
		
			$vt_id = $saferpayID;
			$vt_token = $saferpayToken;
		
			// **************************************************
			// * Put all attributes together and create hosting PayComplete URL 
			// * For hosting: each attribute which could have non-url-conform characters inside should be urlencoded before
			// **************************************************
		
			$paycomplete_url = $saferpay_paycomplete_gateway . "?ACCOUNTID=" . $saferpayAccountID; 
			$paycomplete_url .= "&ID=" . urlencode($vt_id) . "&TOKEN=" . urlencode($saferpayToken);
			
		
			// **************************************************
			// * Special for testaccount: Passwort for hosting-capture neccessary.
			// * Not needed for standard-saferpay-eCommerce-accounts
			// **************************************************
		
			if( (substr($saferpayAccountID, 0, 6) == "99867-") AND ($saferpayTestsystem == "1") ) {
				$paycomplete_url .= "&spPassword=".$saferpayPassword;
			}
			
			// **************************************************
			// * Call the Capture URL from the saferpay hosting server 
			// **************************************************
			// * Initialize CURL session
			// **************************************************
			
			$cs = curl_init($paycomplete_url);
			
			// **************************************************
			// Set CURL-session options
			// **************************************************
		
			curl_setopt($cs, CURLOPT_PORT, 443);			// set option for outgoing SSL requests via CURL
			curl_setopt($cs, CURLOPT_SSL_VERIFYPEER, false);	// ignore SSL-certificate-check - session still SSL-safe
			curl_setopt($cs, CURLOPT_HEADER, 0);			// no header in output
			curl_setopt ($cs, CURLOPT_RETURNTRANSFER, true);	// receive returned characters
		
			// **************************************************
			// Execute CURL-session
			// **************************************************
						
			$answer = curl_exec($cs);
		
			// **************************************************
			// End CURL-session
			// **************************************************
		
			curl_close($cs); 
		
			// **************************************************
			// Stop if capture is not successful
			// **************************************************
		
		
			if( strtoupper( $answer ) == "OK" ) {
				
				// **************************************************
				// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				//
				// If you reach this line - the payment is complete
				//
				// Real money-transfer will be activated within the next batch-closure.
				// Either manually via Saferpay-Backoffice or automatically if 
				// configured in the account-setup of the Saferpay-Backoffice
				//
				// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				// **************************************************
				
				$sql = "UPDATE saferpay_orders SET saferpay_complete = '1', saferpay_complete_result = '$answer', last_modified = '".date("Y-m-d H:i:s")."' WHERE orders_id = '$transactionID'";
				$payment->sDB_CONNECTION->Execute($sql);			
		
				$status = 12;
				$sql = "UPDATE s_order SET cleared= '$status' WHERE transactionID = '$transactionID'";
				
				$payment->sDB_CONNECTION->Execute($sql);					
								
			} else {
				$saferpayAnswer = "FAILURE";
				$saferpayMessage = $answer;
			}	
				
				 
				 
	    } else {
	      echo $error[0];
	    }
	}
}

$sql = '
  SELECT  s_order.id AS id,
          s_order.ordernumber AS ordernumber,
          s_order.transactionID AS transactionID,
          s_order.paymentID,
          s_order.userID,
          s_order.invoice_amount,
		  s_order.currency,
	   	  s_core_paymentmeans.description,
          DATE_FORMAT(s_order.ordertime,"%d.%m.%Y %H:%i") AS ordertimeFormated,
          saferpay_orders.saferpay_id,
          saferpay_orders.saferpay_token,
          saferpay_orders.saferpay_complete,
          saferpay_orders.saferpay_amount
  FROM s_order, saferpay_orders, s_core_paymentmeans
  WHERE s_order.transactionID = saferpay_orders.orders_id
  AND s_order.ordernumber = "'.addslashes($_REQUEST['ordernr']).'"
  AND s_core_paymentmeans.id = s_order.paymentID
	LIMIT 1
  ';
//print_r($sql);

$ordernumber = $_REQUEST['ordernr'];
$queryOrders = mysql_query($sql);
?>
<style>
td {
	font-size:10px;
}
input {
	height:20px;
}
</style>
<body>
<?php
if (@mysql_num_rows($queryOrders)){
  $order=mysql_fetch_array($queryOrders);

	$queryCustomer = mysql_query("
	SELECT firstname, lastname, company FROM s_user_billingaddress WHERE userID={$order["userID"]}
	");

	if (@mysql_num_rows($queryCustomer)){
		$userdata = mysql_fetch_array($queryCustomer);
		$customer = $userdata["company"] ? $userdata["company"] : $userdata["firstname"]." ".$userdata["lastname"];
	}else {
		$customer = "ERROR";
	}
  
	$currCodeType = $order['currency'];
  	
  ?>
  <form id="bookOrder" method="POST" action="">
  <input type="hidden" name="ordernr" value="<?php echo $_REQUEST['ordernr']?>">
  <input type="hidden" name="orderCommit" value="1">
  <table cellpadding="2" cellspacing="2">
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_customer"] ?></strong></td>
    <td><?php echo $customer;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_order_date"] ?></strong></td>
    <td><?php echo $order['ordertimeFormated'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_order_number"] ?></strong></td>
    <td><?php echo $order['ordernumber'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_Transaction_number"] ?></strong></td>
    <td><?php echo $order['transactionID'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_total"] ?></strong></td>
    <td><?php echo number_format($order["invoice_amount"], 2, '.', '').' '.$currCodeType;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_payment"] ?></strong></td>
    <td><?php echo $order['description'];?></td>
  </tr>
  <?php if ($saferpayAnswer=="FAILURE") {?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_error_booking"] ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_error_short_message"] ?></strong></td>
    <td><?php echo $saferpayMessage;?></td>
  </tr>
  <?php }?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <?php if ($order['saferpay_complete']==1) {?>
  <tr>
    <td><strong><?php echo $sLang["saferpayreserveorder"]["action_transaction_Booking_already_done"] ?></strong></td>
    <td>&nbsp;</td>
  </tr>
  <?php } else {?>
  <tr>
  <td colspan="2">
   	<div class="buttons" id="buttons" style="width:150px">
  		<ul>
  		  <li style="display: block;" class="buttonTemplate" id="add"><button onclick="$('bookOrder').submit();" class="button" id="book" name="" type="button" value="" class="button"><div class="buttonLabel">Buchen</div></button></li>
  		</ul>
  	</div>
  </td>
  </tr>
  <?php }?>
  </table>
  </form>
  <?php
} else {
  die($sLang["saferpayreserveorder"]["action_transaction_no_order_found"]);
}
?>
</body>
</html>