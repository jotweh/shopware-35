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
// *****************
?>
<?php
if (!$_GET["id"]) die("NO USER");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Reorder TreePanel</title>
<!-- Common Styles for the examples -->
</head>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/plugins/moo.table/mootable.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<?php

?>
<script type="text/javascript" src="../../../backend/plugins/moo.table/mootable.js"></script>

<?php
// Realy simple search by Char

$sql = "
SELECT s_order.id AS id, ordernumber, userID, invoice_amount, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated, status, description, cleared FROM s_order, s_core_states 
WHERE userID={$_GET["id"]}
AND s_core_states.id = s_order.status
AND s_order.status!=-1
ORDER BY ordertime DESC
";
$queryOrders = mysql_query($sql);




?>

<?php
if (!mysql_num_rows($queryOrders)){
	echo $sLang["userdetails"]["orders_no_orders"];
}
?>
<script type='text/javascript'>	


<?php
if (!mysql_num_rows($queryOrders)){
	// No search-results
	
?>

<?php
} else {
?>
var headers = [
{
"text":"<?php echo $sLang["userdetails"]["orders_date"] ?>",
"key":"regdate","sortable":true,
"fixedWidth":true,"defaultWidth":"125px","date2":true},
{
"text":"<?php echo $sLang["userdetails"]["orders_ordernumber"] ?>",
"key":"ordernumber","sortable":true,
"fixedWidth":true,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["userdetails"]["orders_orderstatus"] ?>",
"key":"state","sortable":true,
"fixedWidth":true,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["userdetails"]["orders_payment_status"] ?>",
"key":"state2","sortable":true,
"fixedWidth":true,"defaultWidth":"90px"},
{"text":"<?php echo $sLang["userdetails"]["orders_amount"] ?>",
"key":"amount","sortable":true,
"fixedWidth":true,"defaultWidth":"100px", "numeric":true}
];
<?php
	// Display search-results
	echo "var data = [";
	$countOrders = mysql_num_rows($queryOrders);
	$i = 0;
	while ($order=mysql_fetch_array($queryOrders)){
		// count positions
		
		$queryCustomer = mysql_query("
		SELECT firstname, lastname, company FROM s_user_billingaddress WHERE userID={$order["userID"]}
		");
		
		if (@mysql_num_rows($queryCustomer)){
			$userdata = mysql_fetch_array($queryCustomer);
			
			
			
			$customer = $userdata["company"] ? $userdata["company"] : $userdata["firstname"]." ".$userdata["lastname"];
			
			
		}else {
			$customer = "ERROR";
		}
		
		$invoiceTotal += $order["invoice_amount"];
		$i++;
		//$order["ordernumber"] = sprintf("%06d", $order["ordernumber"]);
		$comma = $i == $countOrders ? "" : ",";
		$status = $order["description"];
		//$status  = wordwrap($status,12," ");
		// Query Payment - State
		$getPaymentInfo = mysql_query("SELECT description FROM s_core_states WHERE id = {$order["cleared"]}");
		$getPaymentInfo = mysql_fetch_array($getPaymentInfo);
		$payment = $getPaymentInfo["description"];
		if (!$order["cleared"]) $order["cleared"] = 17;
		$customer = htmlspecialchars($customer);
?>

	{"orderID":"<?php echo $order["id"]?>","stateNumeric":"<?php echo $order["status"] ?>","paymentNumeric":"<?php echo $order["cleared"] ?>","regdate":"<?php echo $order["ordertimeFormated"]?>","ordernumber":"<a onclick=\"parent.parent.loadSkeleton('orders',false, {'id':'<?php echo $order["id"] ?>'})\" class=\"ico information\" style=\"cursor:pointer\"></a><?php echo $order["ordernumber"]?>","state":"<?php echo $status ?>","state2":"<?php echo $payment ?>","amount":"<?php echo number_format($order["invoice_amount"], 2, '.', '')?> &euro;"}<?php echo $comma ?>
<?php
	} // for each user
	echo "];";
} // In case of any result

?>


window.addEvent('load',function(){
				mootable = new MooTable( 'test', {debug: false, height: '270px', headers: headers, sortable: true, useloading: false, resizable: false});
				mootable.addEvent( 'afterRow', function(data, row){					
				
					// Remove formating from status-value
					row.cols[2].element.setHTML(row.cols[2].value);
					row.cols[3].element.setHTML(row.cols[3].value);
					// Markup row if status is not eqal finished
					switch (row.data.stateNumeric){
						case "2":
							row.cols[2].element.setStyle('color','#009933');
							break;
						case "7":
							row.cols[2].element.setStyle('color','#009933');
							break;
						case "12":
							row.cols[2].element.setStyle('color','#009933');
							break;
						default:
							row.cols[2].element.setStyle('color','#FF0000');
							break;
					}
					
					// Markup - payment row 
					switch (row.data.paymentNumeric){
						case "12":
							row.cols[3].element.setStyle('color','#009933');
							break;
						case "11":
							row.cols[3].element.setStyle('color','#009933');
							break;
						default:
							row.cols[3].element.setStyle('color','#FF0000');
							break;
					}
					
					
				
				});
				mootable.loadData( data );
				});
</script>
<?php
// If category is choosen

?>

<body id="case1">

<div id='test'></div>
</body>
</html>
