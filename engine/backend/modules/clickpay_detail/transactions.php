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
session_write_close();
mysql_close();

$sLang["clickpay_detail"]["action_transaction_error_booking"] = "Es ist ein Fehler bei der Buchung aufgetreten";
$sLang["clickpay_detail"]["action_transaction_error_short_message"] = "Fehler";
$sLang["clickpay_detail"]["action_transaction_customer"] = "Kunde:";
$sLang["clickpay_detail"]["action_transaction_order_date"] = "Bestelldatum:";
$sLang["clickpay_detail"]["action_transaction_order_number"] = "Bestellnummer:";
$sLang["clickpay_detail"]["action_transaction_Transaction_number"] = "Transaktionsnr.:";
$sLang["clickpay_detail"]["action_transaction_total"] = "Gesamtbetrag:";
$sLang["clickpay_detail"]["action_transaction_payment"] = "Zahlverfahren:";
$sLang["clickpay_detail"]["action_transaction_Booking_already_done"] = "Buchung wurde durchgef&uuml;hrt!";
$sLang["clickpay_detail"]["action_transaction_no_order_found"] = "Keine Bestellung gefunden!";

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/connectors/clickpay/clickpay.class.php');

$sClickPay = new sClickPay(false);
$sErrorMessages = array();

if(!empty($_REQUEST['sAction']))
{
	$sParams = array();
	$sParams['kontaktid'] = $_REQUEST['transactionID'];
	$sParams['werbecode'] = $_REQUEST['werbecode'];
	
	switch ($_REQUEST['sAction']) {
		case 'book':
			$sRequestURL = 'https://www.eos-payment.de/kartebuch.acgi';
			$sParams['spb_betrag'] = $sClickPay->sFormatPrice($_REQUEST['amount']);
			$sParams['spb_warten'] = 25;
			$sParams['BuchungDelayTage'] = (int)$_REQUEST['delay'];
			$sql = '
				UPDATE eos_reserved_orders
				SET	bookdate='.$sClickPay->sDB_CONNECTION->OffsetDate($sParams['BuchungDelayTage']).',
				bookvalue=?, `changed`=NOW()
				WHERE transactionID=?
			';
			$sClickPay->sDB_CONNECTION->Execute($sql,array(
				str_replace(',','.',$sParams['spb_betrag']),
				$sParams['kontaktid']
			));
			break;
		case 'cancel_reservation':
		case 'cancel':
			$sRequestURL = 'https://www.eos-payment.de/KarteStorno.acgi';
			$sParams['spr_warten'] = 25;
			break;
		default:
			break;
	}
	$sRespone = $sClickPay->sDoRequest($sRequestURL,$sParams);
	if(!empty($sRespone['status'])&&$sRespone['status']=='ERROR')
	{
		$fields = array('spb_betrag','spb_warten','spb_referenz','spr_warten','Fehlercode','kontaktid','werbecode');
		foreach ($fields as $field)
		{
			if(!empty($sRespone[$field]))
			{
				$sErrorMessage = $sClickPay->sGetClickPayErrorMessage($sRespone[$field],$field);
				if(!empty($sErrorMessage))
					$sErrorMessages[] = $sErrorMessage;
			}
		}
	}
	sleep(3);
}
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
          e.werbecode,
          e.transactionID,
          e.reference,
          e.status as eos_status,
          bookdate,
          bookvalue
  FROM s_order, eos_reserved_orders e, s_core_paymentmeans
  WHERE s_order.transactionID = e.transactionID
  AND s_order.id = '.intval($_REQUEST['orderID']).'
  AND s_core_paymentmeans.id = s_order.paymentID
	LIMIT 1
  ';
$order=$sClickPay->sDB_CONNECTION->GetRow($sql);
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
if (!empty($order)){
	
	$userdata=$sClickPay->sDB_CONNECTION->GetRow($sql);

	if (!empty($userdata)){
		$customer = $userdata["company"] ? $userdata["company"] : $userdata["firstname"]." ".$userdata["lastname"];
	}else {
		$customer = "ERROR";
	}
  
	$currCodeType = $order['currency'];
  	
  ?>
  <form id="bookOrder" method="POST" action="">
  <input type="hidden" name="orderID" value="<?php echo $_REQUEST['orderID']?>">
  <input type="hidden" name="werbecode" value="<?php echo $order['werbecode']?>">
  <input type="hidden" name="transactionID" value="<?php echo $order['transactionID']?>">
  <input type="hidden" name="reference" value="<?php echo $order['reference']?>">
  <table cellpadding="2" cellspacing="2" width="100%">
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_customer"] ?></strong></td>
    <td><?php echo $customer;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_order_date"] ?></strong></td>
    <td><?php echo $order['ordertimeFormated'];?></td>
  </tr>
  <?php if(!empty($order['ordernumber'])):?>
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_order_number"] ?></strong></td>
    <td><?php echo $order['ordernumber'];?></td>
  </tr>
  <?php endif;?>
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_Transaction_number"] ?></strong></td>
    <td><?php echo $order['transactionID'];?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_total"] ?></strong></td>
    <td><?php echo $sClickPay->sFormatPrice($order["invoice_amount"]).' '.$currCodeType;?></td>
  </tr>
  <tr>
    <td><strong><?php echo $sLang["clickpay_detail"]["action_transaction_payment"] ?></strong></td>
    <td><?php echo $order['description'];?></td>
  </tr>
  <tr>
    <td><strong>ClickPay Status</strong></td>
    <td><?php echo $sClickPay->sGetClickPayStatusMessage($order['eos_status']);?></td>
  </tr>
  <?php if(!empty($order['bookdate'])):?>
  <tr>
    <td><strong>Buchung am:</strong></td>
    <td><?php echo date('d.m.Y',strtotime($order['bookdate']));?></td>
  </tr>
  <tr>
    <td><strong>Buchungsbetrag:</strong></td>
    <td><?php echo $sClickPay->sFormatPrice($order["bookvalue"]);?> <?php echo $currCodeType;?></td>
  </tr>
  <?php endif;?>
  <?php if (!empty($sErrorMessages)) {?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><strong><?php echo $sLang["clickpay_detail"]["action_transaction_error_booking"] ?>:</strong></td>
  </tr>
  <tr>
  <?php foreach ($sErrorMessages as $sErrorMessage):?>
    <td colspan="2"><?php echo $sErrorMessage;?></td>
  <?php endforeach;?>
  </tr>
  <?php }?>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <?php if ($order['eos_status']==2) {?>
  <tr>
  <td colspan="2">
   	<div class="buttons" id="buttons" style="width:120px;float:right">
  		<ul>
  		  <li style="display: block;" class="buttonTemplate" id="add"><button class="button" id="book" name="sAction" type="submit" value="cancel_reservation" class="button"><div class="buttonLabel">Stornieren</div></button></li>
  		</ul>
  	</div>
  </td>
  </tr>
  <?php } elseif ($order['eos_status']==1) {?>
  <tr>
    <td><strong>Betrag</strong></td>
    <td><input style="text-align:right;padding:2px 5px 0;" type="text" name="amount" value="<?php echo $sClickPay->sFormatPrice($order["invoice_amount"]);?>">&nbsp;&nbsp;<?php echo $currCodeType;?></td>
  </tr>
  <tr>
    <td><strong>Verzögertes Buchen</strong></td>
    <td><input style="text-align:right;padding:2px 5px 0;" type="text" name="delay" value="0">&nbsp;&nbsp;Tage</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
  <td colspan="2">
    <div class="buttons" style="width:90px;float:right">
  		<ul>
  		  <li style="display: block;" class="buttonTemplate" id="add">
  		  	<button class="button" id="book" name="sAction" type="submit" value="book" class="button"><div class="buttonLabel">Buchen</div></button>
  		  </li>
  		</ul>
  	</div>
   	<div class="buttons" style="width:120px;float:right">
  		<ul>
  		  <li style="display: block;" class="buttonTemplate" id="add">
  		  	<button class="button" id="book" name="sAction" type="submit" value="cancel" class="button"><div class="buttonLabel">Stornieren</div></button>
  		  </li>
  		</ul>
  	</div>

  </td>
  </tr>
  <?php }?>
  </table>
  </form>
  <?php
} else {
  die($sLang["clickpay_detail"]["action_transaction_no_order_found"]);
}
?>
</body>
</html>