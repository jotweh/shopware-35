<?php
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
error_reporting(0);
ini_set("display_errors",0);
$path = "../../../connectors/";	
include("../../../connectors/paypalexpress/paypalexpress.class.php");	

/*Enter your E-Mail for debugging*/
$debug = ""; 
$payment = new paypalexpressPayment($debug,"../../../connectors/");										

/*Enable PayPal logging = "1"*/
$paypalLogging = "0";

include("../../../connectors/paypalexpress/CallerService.php");

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
		
	$ordernumber = $_REQUEST["ordernr"];

	$error = array();

	$queryOrder = mysql_query("SELECT transactionID FROM s_order WHERE ordernumber = {$ordernumber}");

	if (mysql_num_rows($queryOrder)>0){
  		$queryOrder = mysql_fetch_array($queryOrder);
  		$transactionID = $queryOrder['transactionID'];
	} else {
	  $error[] = "<script>parent.parent.Growl('".$sLang["paypalreserveorder"]["transactions_no_orders_found"]."')</script>";	
	}
	
	
	$queryOrder = mysql_query("SELECT * FROM paypal_orders WHERE stransId = '{$transactionID}'");

	if (mysql_num_rows($queryOrder)>0){

	  	$queryOrder = mysql_fetch_array($queryOrder);
			        
	    $amount = (double)($_POST["amount"]);
	    
		// Vermeidung von Rundungsfehlern
		$paypalPrice = $queryOrder['price'] * 100;
		$refundedAmount = $queryOrder['refunded'] * 100;
		$refundRequestAmount = $amount * 100;

	    if ($refundRequestAmount > ($paypalPrice - $refundedAmount)) {
	      $error[] = "<script>parent.parent.Growl('".$sLang["paypalreserveorder"]["action_transaction_too_high"]."')</script>";
	    }

	    if (!($refundRequestAmount > 0) && empty($refundRequestAmount)) {
	      $error[] = "<script>parent.parent.Growl('".$sLang["paypalreserveorder"]["action_refund_error"]."')</script>";
	    }

	    
	    if (empty($error)) {

		    $paypalTransactionID = $queryOrder['transactionId'];
		    $paymentAmount = $queryOrder['price'];
		    $currCodeType = $queryOrder['currency'];
						
			$refundAmount = ($refundedAmount + $refundRequestAmount)/100;
			
			// Start Refund						
			$serverName = $_SERVER['SERVER_NAME'];
			$url= "http://".$serverName."/";
					
			// URL for receiving Instant Payment Notification (IPN) about this refund.
			$notifyURL = $url.'engine/connectors/paypalexpress/ipn.php';
			  		
			if ($queryOrder['price'] == $amount) {
			   $nvpstr="&TRANSACTIONID=".$paypalTransactionID."&REFUNDTYPE=Full&NOTIFYURL=".$notifyURL;	
			} else {
		   		$nvpstr="&TRANSACTIONID=".$paypalTransactionID."&REFUNDTYPE=Partial&AMT=".$amount."&CURRENCYCODE=".$currCodeType."&NOTIFYURL=".$notifyURL;		
			}

			/*Log PayPal Request*/
			if ($paypalLogging == "1") {
				$payment->sLog("Request RefundTransaction", $nvpstr, true);			
			}
		
		   $resArray=hash_call("RefundTransaction",$nvpstr);
			//End do Refund

			/*Log PayPal Response*/
			if ($paypalLogging == "1") {
				foreach($resArray as $schluessel => $wert) $tmpRes.= $schluessel.": ".$wert."\n";
				$payment->sLog("Response RefundTransaction", $tmpRes, true);				
			}
				
		   $ack = strtoupper($resArray["ACK"]);
		   $paymentStatus = strtoupper($resArray["PAYMENTSTATUS"]);
			 
		   if($ack=="SUCCESS"){
		
				// Paypal Transaktion ID
				$paypalTransactionId = urldecode($resArray["REFUNDTRANSACTIONID"]);
		
				$sql = "UPDATE paypal_orders SET paymentStatus = 'Refunded', refunded = '$refundAmount' WHERE stransId = '$transactionID'";
				$sql1 = $sql;
				$payment->sDB_CONNECTION->Execute($sql);			
				$status = 20;
				$refundComment = $sLang["paypalreserveorder"]["action_refund_comment"].$amount.' '.$currCodeType;
				$sql = "UPDATE s_order SET cleared= '$status', comment = '$refundComment' WHERE transactionID = '$transactionID'";				
				$sql2 = $sql;
				$payment->sDB_CONNECTION->Execute($sql);
			} else  {
		
				// Errorcode und Error Messages!
				$errorCode = $resArray["L_ERRORCODE0"];
				$shortMessage = $resArray["L_SHORTMESSAGE0"];
				$longMessage = $resArray["L_LONGMESSAGE0"];		
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
          s_core_paymentmeans.description,
          DATE_FORMAT(s_order.ordertime,"%d.%m.%Y %H:%i") AS ordertimeFormated,
          paypal_orders.transactionId AS paypalTransactionID,
          paypal_orders.paymentStatus,
          paypal_orders.authorization,
          paypal_orders.booked,
          paypal_orders.price AS amount,
          paypal_orders.refunded AS refund_amount,
          paypal_orders.currency
  FROM s_order, paypal_orders, s_core_paymentmeans
  WHERE s_order.transactionID = paypal_orders.stransId
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
  
	$refundMax = $order['amount'] - $order['refund_amount'];

  ?>
  <form id="bookOrder" method="POST" action="">
  <input type="hidden" name="ordernr" value="<?php echo $_REQUEST['ordernr']?>">
  <input type="hidden" name="orderCommit" value="1">
  <table cellpadding="2" cellspacing="2">
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_customer"] ?></strong></td>
    <td><?php echo $customer;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_order_date"] ?></strong></td>
    <td><?php echo $order['ordertimeFormated'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_order_number"] ?></strong></td>
    <td><?php echo $order['ordernumber'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_Transaction_number"] ?></strong></td>
    <td><?php echo $order['transactionID'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_total"] ?></strong></td>
    <td><?php echo number_format($order["invoice_amount"], 2, '.', '')." ".$order['currency'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_refund_total"] ?></strong></td>
    <td><?php echo $order['refund_amount']." ".$order['currency'];;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_payment"] ?></strong></td>
    <td><?php echo $order['description'];?></td>
  </tr>
  <?php if ($ack=="FAILURE") {?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_error_booking"] ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_error_errorcode"] ?></strong></td>
    <td><?php echo $errorCode;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_error_short_message"] ?></strong></td>
    <td><?php echo $shortMessage;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_transaction_error_long_message"] ?></strong></td>
    <td><?php echo $longMessage;?></td>
  </tr>
  <?php }?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <?php if (($ack=="SUCCESS") && ($order['amount'] > $order['refund_amount'])){?>
  <tr>
    <td colspan="2"><strong><?php echo $sLang["paypalreserveorder"]["action_refund_done"] ?></strong></td>
  </tr>
  <?php }?>
  <?php if ($order['amount'] > $order['refund_amount']){?>
  <tr>
    <td><strong><?php echo $sLang["paypalreserveorder"]["action_refund_max_amount"] ?></strong></td>
    <td><input type="text" name="amount" value="<?php echo $refundMax;?>" style="width:50px"></td>
  </tr>
  <tr>
  <td colspan="2">
   	<div class="buttons" id="buttons" style="width:150px">
  		<ul>
  		  <li style="display: block;" class="buttonTemplate" id="add"><button onclick="$('bookOrder').submit();" class="button" id="book" name="" type="button" value="" class="button"><div class="buttonLabel">Gutschrift</div></button></li>
  		</ul>
  	</div>
  </td>
  </tr>
  <?php }else {?>
  <tr>
    <td colspan="2"><strong><?php echo $sLang["paypalreserveorder"]["action_refund_done"] ?></strong></td>
  </tr>
  <?php
  }
  ?>
  </table>
  </form>
  <?php
} else {
  die($sLang["paypalreserveorder"]["action_transaction_no_order_found"]);
}
?>
</body>
</html>