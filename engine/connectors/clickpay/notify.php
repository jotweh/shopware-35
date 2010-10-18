<?php
//ob_start();
require_once('clickpay.class.php');
$sClickPay = new sClickPay();

if($_REQUEST['sAction']=='status'&&in_array($_REQUEST['sStatus'],array(1,2)))
{
	$status = $_REQUEST['sStatus']==1 ? 18 : 12;
	$sClickPay->sSubmitOrder($status);
}
if(!empty($_REQUEST['sStatus'])&&!empty($_REQUEST['sTransactionID']))
{
	$sql = 'UPDATE eos_reserved_orders SET status=?, `changed`=NOW() WHERE transactionID=?';
	$sClickPay->sDB_CONNECTION->Execute($sql,array($_REQUEST['sStatus'],$_REQUEST['sTransactionID']));
}
?>