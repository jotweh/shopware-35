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

// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);

if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 1;
}

$nodes = array();
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

if ($_POST["pagingID"]) $_POST["id"] = $_POST["pagingID"];
if (!$_POST["id"]) $_POST["id"] = 3;
if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "id";
if ($_POST["sort"]=="regdate") $_POST["sort"] = "firstlogin";
if (!$_POST["dir"]) $_POST["dir"] = "ASC";

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
		s_user.email LIKE '$search'
	OR 
		s_user_billingaddress.lastname LIKE '$search'
	OR
		s_user_billingaddress.company LIKE '$search'
	OR 
		s_user_billingaddress.customernumber LIKE '$search'
	) 
	";
	
}
if ($_POST["group"]){
	$_POST["group"] = mysql_real_escape_string($_POST["group"]);
	
	$groupSQL = "
	 AND s_user.customergroup='{$_POST["group"]}' 
	";
}



$sql = "
	SELECT DISTINCT s_user.id AS id, DATE_FORMAT(firstlogin,'%d.%m.%Y') AS regdate, company, firstname, lastname,customernumber, zipcode, city
	FROM s_user, s_user_billingaddress WHERE 
	s_user.id = s_user_billingaddress.userID $searchSQL $groupSQL
	";


$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_num_rows($resultCountArticles);

$sql = "
SELECT s_user.id AS id, DATE_FORMAT(firstlogin,'%d.%m.%Y') AS regdate, company, firstname, lastname
,customernumber,  zipcode, city, SUM(s_order.invoice_amount) AS amount, COUNT(s_order.id) AS countOrders,s_core_customergroups.description AS customergroup
FROM s_user
LEFT JOIN s_order
ON s_order.userID = s_user.id AND s_order.ordernumber!='' AND s_order.status != -1 AND s_order.status != 4 
LEFT JOIN s_core_customergroups ON s_user.customergroup = s_core_customergroups.groupkey
,s_user_billingaddress
WHERE 
s_user.id = s_user_billingaddress.userID  
$searchSQL $groupSQL
GROUP BY s_user.id
ORDER BY {$_POST["sort"]} {$_POST["dir"]}
LIMIT {$_POST["start"]},{$_POST["limit"]}
";

$getUser = mysql_query($sql);
//echo $sql;

if (@mysql_num_rows($getUser)){
	while ($user=mysql_fetch_assoc($getUser)){
		$user["city"] = utf8_encode($user["city"]);
		$user["lastname"] = utf8_encode($user["lastname"]);
		$user["firstname"] = utf8_encode($user["firstname"]);
		$user["zipcode"] = utf8_encode($user["zipcode"]);
		$user["customergroup"] = utf8_encode($user["customergroup"]);
		$user["company"] = utf8_encode($user["company"]);
		
		$user["amount"] = $sCore->sFormatPrice(round($user["amount"],2));
		$nodes["user"][] = $user;
	}
}else {
	$nodes["user"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>