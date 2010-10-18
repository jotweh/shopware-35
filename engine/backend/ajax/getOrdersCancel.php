<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}
// *****************
?>
<?php
require_once("json.php");
$json = new Services_JSON();
?>
<?php

$nodes = array();

if (!in_array($_POST["sort"],array("ordertime"))){
	unset($_POST["sort"]);
}

$_POST["start"] = intval($_POST["start"]);

$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

// Filters
if (isset($_POST["filterState"]))   $_POST["filterState"] = intval($_POST["filterState"]);		// Allg. Bestellstatus
if (isset($_POST["filterPayment"])) $_POST["filterPayment"] = intval($_POST["filterPayment"]);	// Zahlungsart
if (isset($_POST["filterCleared"])) $_POST["filterCleared"] = intval($_POST["filterCleared"]); 	// Zahlstatus

$_POST["filterState"] = -1;

if ($_POST["filterPayment"]==-1) unset($_POST["filterPayment"]);
if ($_POST["filterCleared"]==-1) unset($_POST["filterCleared"]);

if ($_POST["startDate"] && $_POST["endDate"]){
	$filterDate = 
	"
	AND (TO_DAYS(s_order.ordertime) >= TO_DAYS('{$_POST["startDate"]}') AND TO_DAYS(s_order.ordertime) <= TO_DAYS('{$_POST["endDate"]}'))
	";
}

if (isset($_POST["filterState"])){
	if (!$_POST["filterstate"]) $_POST["filterstate"] = "0";
	$filterState = "
	AND s_order.status = {$_POST["filterState"]}
	";
}

if (isset($_POST["filterCleared"])){
	if (!$_POST["filterCleared"]) $_POST["filterCleared"] = "0";
	$filterCleared = "
	AND s_order.cleared = {$_POST["filterCleared"]}
	";
}

if (isset($_POST["filterPayment"])){
	if (!$_POST["filterPayment"]) $_POST["filterPayment"] = "0";
	$filterPayment = "
	AND s_order.paymentID = {$_POST["filterPayment"]}
	";
}

if (!$_POST["limit"]) $_POST["limit"] = 25;

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "ordertime";
if (!$_POST["dir"]) $_POST["dir"] = "DESC";

if ($_POST["search"]){
	if (strlen($_POST["search"])>1){
		$search = "%".mysql_real_escape_string($_POST["search"])."%";
	}else {
		$search = mysql_real_escape_string($_POST["search"])."%";
	}
	$searchSQL = "
	AND 
	(
		s_order.ordernumber LIKE '$search%'
	OR
		s_order.transactionID LIKE '$search%'
	OR 
		s_user_billingaddress.lastname LIKE '$search%'
	OR
		s_user_billingaddress.company LIKE '$search%'
	OR 
		s_user_billingaddress.customernumber LIKE '$search%'
	) 
	";
}


/*
Get all current used order-states
*/
$sql = "
SELECT DISTINCT status FROM s_order 
WHERE status = -1
ORDER BY status ASC
";
$getPossibleStates = mysql_query($sql);
while ($state = mysql_fetch_assoc($getPossibleStates)){
	$i++;
	$statusColumns[] = "COUNT(scs$i.id) AS status1";
	$statusJoins .= "LEFT JOIN s_core_states AS scs$i ON scs$i.id = s_order.status AND scs$i.id=".$state["status"]."\n";
	$statusKeys[] = $state["status"];
}

/*
Get all current used payment-states
*/
$sql = "
SELECT DISTINCT paymentID FROM s_order 
ORDER BY paymentID ASC
";
$getPossiblePayments = mysql_query($sql);
unset($i);
while ($payment = mysql_fetch_assoc($getPossiblePayments)){
	$i++;
	$paymentColumns[] = "COUNT(paytab$i.id) AS payment{$payment["paymentID"]}";
	$paymentJoins .= "LEFT JOIN s_core_paymentmeans AS paytab$i ON paytab$i.id = s_order.paymentID AND paytab$i.id=".$payment["paymentID"]."\n";
	$payKeys[] = $payment["paymentID"];
}


/*
Get all current used payment-means
*/
$sql = "
SELECT COUNT(*) AS count, SUM(invoice_amount) AS amount, ".implode(",",$statusColumns).", ".implode(",",$paymentColumns)."
FROM s_order
LEFT JOIN s_user_billingaddress ON s_user_billingaddress.userID = s_order.userID
$statusJoins
$paymentJoins
WHERE 
	s_order.status = -1
$filterState
$filterCleared
$filterPayment
$filterDate
$searchSQL
";

$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_result($resultCountArticles,0,"count");
$nodes["totalAmount"] = @mysql_result($resultCountArticles,0,"amount");
foreach ($statusKeys as $statusKey) $nodes["status".$statusKey] = @mysql_result($resultCountArticles,0,"status".$statusKey);
foreach ($payKeys as $payKey) $nodes["payment".$payKey] = @mysql_result($resultCountArticles,0,"payment".$payKey);


$sql = "
SELECT s_order.id AS id,email,phone, comment,currency,currencyFactor,firstname,scs1.description AS statusDescription,scs2.description AS clearingDescription, lastname, company, s_order.subshopID AS subshopID, s_order.paymentID AS paymentID, s_core_paymentmeans.description AS paymentDescription, ordernumber, transactionID, s_order.userID AS userID, invoice_amount,invoice_shipping, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated, status, cleared 
FROM s_order
LEFT JOIN s_user ON s_user.id = s_order.userID
LEFT JOIN s_user_billingaddress ON s_user_billingaddress.userID = s_user.id
LEFT JOIN s_core_paymentmeans ON s_core_paymentmeans.id = s_order.paymentID
LEFT JOIN s_core_states AS scs1 ON scs1.id = s_order.status
LEFT JOIN s_core_states AS scs2 ON scs2.id = s_order.cleared
WHERE 
	s_order.status = -1
$filterState
$filterCleared
$filterPayment
$filterDate
$searchSQL
ORDER BY {$_POST["sort"]} {$_POST["dir"]}
LIMIT {$_POST["start"]},{$_POST["limit"]}
";
$getOrders = mysql_query($sql);
//echo $sql;

if (@mysql_num_rows($getOrders)){
	while ($order=mysql_fetch_assoc($getOrders)){
		$order["paymentDescription"] = utf8_encode($order["paymentDescription"]);
		$order["comment"] = utf8_encode(strip_tags($order["comment"]));
		$order["customer"] = utf8_encode($order["company"] ? $order["company"] : $order["firstname"]." ".$order["lastname"]);
		$amount = round($order["invoice_amount"],2);
		$amount = $sCore->sFormatPrice($amount);
		$order["invoice_amount"] = $amount;
		// Get order details
		$queryDetails = mysql_query("SELECT articleordernumber,price,quantity,name FROM s_order_details WHERE orderID={$order["id"]}");
		while ($detail = mysql_fetch_assoc($queryDetails)){
			$detail = "<strong>{$detail["quantity"]} * {$detail["name"]} ({$detail["articleordernumber"]})</strong><br />";
			$order["details"] .= utf8_encode($detail);
		}
		$nodes["order"][] = $order;
	}
}else {
	$nodes["order"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>