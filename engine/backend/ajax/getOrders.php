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

require_once("../../backend/ajax/json.php");
$json = new Services_JSON();

$nodes = array();

if (!in_array($_POST["sort"],array("ordertime","ordernumber","invoice_amount","transactionID","dispatch","subshop","status","cleared","paymentDescription","customer"))){
	unset($_POST["sort"]);
}
if ($_POST["sort"]=="customer") $_POST["sort"] = "userID";
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

// Filters
#if (isset($_POST["filterState"]))   $_POST["filterState"] = intval($_POST["filterState"]);		// Allg. Bestellstatus
#if (isset($_POST["filterPayment"])) $_POST["filterPayment"] = intval($_POST["filterPayment"]);	// Zahlungsart
#if (isset($_POST["filterCleared"])) $_POST["filterCleared"] = intval($_POST["filterCleared"]); 	// Zahlstatus

if ($_POST["filterState"]==-1 || $_POST["filterState"]==='') unset($_POST["filterState"]);
if ($_POST["filterPayment"]==-1 || $_POST["filterPayment"]==='') unset($_POST["filterPayment"]);
if ($_POST["filterCleared"]==-1 || $_POST["filterCleared"]==='') unset($_POST["filterCleared"]);
if ($_POST["filterGroup"]==-1 || empty($_POST["filterGroup"])) unset($_POST["filterGroup"]);
if ($_POST["filterShop"]==-1 || empty($_POST["filterShop"])) unset($_POST["filterShop"]);
if ($_POST["filterDispatch"]==-1 || empty($_POST["filterDispatch"])) unset($_POST["filterDispatch"]);

if ($_POST["startDate"] && $_POST["endDate"]){
	$filterDate = 
	"
	AND s_order.ordertime >= '{$_POST["startDate"]}'
	AND s_order.ordertime <='{$_POST["endDate"]} 23:59:59'	
	";
}

if (isset($_POST["filterState"])){
	if (!$_POST["filterState"]) $_POST["filterState"] = "0";
	$filterState = "
	AND s_order.status IN ({$_POST["filterState"]})
	";
}

if (isset($_POST["filterCleared"])){
	if (!$_POST["filterCleared"]) $_POST["filterCleared"] = "0";
	$filterCleared = "
	AND s_order.cleared IN ({$_POST["filterCleared"]})
	";
}

if (isset($_POST["filterPayment"])){
	if (!$_POST["filterPayment"]) $_POST["filterPayment"] = "0";
	$filterPayment = "
	AND s_order.paymentID IN ({$_POST["filterPayment"]})
	";
}
if (isset($_POST["filterGroup"])){
	
	$_POST["filterGroup"] = mysql_real_escape_string($_POST["filterGroup"]);
	$filterGroup = "
	AND s_user.customergroup = '{$_POST["filterGroup"]}'
	";
}

if (isset($_POST["filterShop"])){
	
	$_POST["filterShop"] = mysql_real_escape_string($_POST["filterShop"]);
	$filterShop = "
	AND s_order.subshopID = '{$_POST["filterShop"]}'
	";
}

if (isset($_POST["filterDispatch"])){
	
	$_POST["filterDispatch"] = mysql_real_escape_string($_POST["filterDispatch"]);
	$filterDispatch = "
	AND s_order.dispatchID = '{$_POST["filterDispatch"]}'
	";
}
if (!$_POST["limit"]) $_POST["limit"] = 25;

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "ordertime";
if (!$_POST["dir"]) $_POST["dir"] = "DESC";

if ($_POST["search"]){
	$cur_encoding = mb_detect_encoding($_POST["search"]) ;
	if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")){
		$_POST["search"] = utf8_decode($_POST["search"]);
	}
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
WHERE status != -1
ORDER BY status ASC
";
$getPossibleStates = mysql_query($sql);
while ($state = mysql_fetch_assoc($getPossibleStates)){
	$i++;
	$statusColumns[] = "COUNT(scs$i.id) AS status{$state["status"]}";
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
SELECT COUNT(*) AS count, SUM(invoice_amount/currencyFactor) AS amount, ".implode(",",$statusColumns).", ".implode(",",$paymentColumns)."
FROM s_order
LEFT JOIN s_user_billingaddress ON s_user_billingaddress.userID = s_order.userID
LEFT JOIN s_user ON s_user.id = s_order.userID
$statusJoins
$paymentJoins
WHERE 
	s_order.status != -1
$filterState
$filterCleared
$filterPayment
$filterDate
$filterGroup
$filterShop
$filterDispatch
$searchSQL
";

$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_result($resultCountArticles,0,"count");
$nodes["totalAmount"] = @mysql_result($resultCountArticles,0,"amount");
foreach ($statusKeys as $statusKey) $nodes["status".$statusKey] = @mysql_result($resultCountArticles,0,"status".$statusKey);
foreach ($payKeys as $payKey) $nodes["payment".$payKey] = @mysql_result($resultCountArticles,0,"payment".$payKey);

if (!empty($sCore->sCONFIG['sPREMIUMSHIPPIUNG']))
{
	$dispatch_table = 's_premium_dispatch';
}
else
{
	$dispatch_table = 's_shippingcosts_dispatch';
}


$sql = "
SELECT s_order.id AS id, d.name AS dispatch,s_order_documents.id AS sodID, s_core_multilanguage.name AS subshop,currency,currencyFactor,firstname,scs1.description AS statusDescription,scs2.description AS clearingDescription, lastname, company, s_order.subshopID, s_order.paymentID, s_core_paymentmeans.template AS paymentTpl,s_core_paymentmeans.description AS paymentDescription, ordernumber, transactionID, s_order.userID AS userID, invoice_amount,invoice_shipping, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertimeFormated,taxfree, status, cleared, trackingcode
FROM s_order
USE INDEX ( ordertime ) 
LEFT JOIN s_user_billingaddress ON s_user_billingaddress.userID = s_order.userID
LEFT JOIN s_user ON s_user.id = s_order.userID
LEFT JOIN s_core_paymentmeans ON s_core_paymentmeans.id = s_order.paymentID
LEFT JOIN s_core_states AS scs1 ON scs1.id = s_order.status
LEFT JOIN s_core_states AS scs2 ON scs2.id = s_order.cleared
LEFT JOIN $dispatch_table d ON d.id = s_order.dispatchID
LEFT JOIN  s_core_multilanguage ON s_core_multilanguage.id = s_order.subshopID
LEFT JOIN s_order_documents ON s_order_documents.orderID = s_order.id
WHERE 
	s_order.status != -1
$filterState
$filterCleared
$filterPayment
$filterDate
$filterGroup
$filterShop
$filterDispatch
$searchSQL
ORDER BY {$_POST["sort"]} {$_POST["dir"]}
LIMIT {$_POST["start"]},{$_POST["limit"]}
";


$getOrders = mysql_query($sql);

if (@mysql_num_rows($getOrders)){
	while ($order=mysql_fetch_assoc($getOrders)){
		if (!$order["currencyFactor"]) $order["currencyFactor"] = 1;
		if (strlen($order["paymentDescription"])>15){
			$order["paymentDescription"] = substr($order["paymentDescription"],0,15);
		}
		if (!empty($order["sodID"])){
			$order["pdf"] = true;
		}else {
			$order["pdf"] = false;
		}
		$order["paymentDescription"] = utf8_encode($order["paymentDescription"]);
		$order["clearingDescription"] = utf8_encode($order["clearingDescription"]);
		$order["paymentTpl"] = utf8_encode($order["paymentTpl"]);
		$order["dispatch"] = utf8_encode($order["dispatch"]);
		$order["customer"] = utf8_encode($order["company"] ? $order["company"] : $order["firstname"]." ".$order["lastname"]);
		$amount = round(($order["invoice_amount"]/$order["currencyFactor"]),2);
		$amount = $sCore->sFormatPrice($amount);
		$order["invoice_amount"] = $amount;
		$nodes["order"][] = $order;
	}
}else {
	$nodes["order"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>