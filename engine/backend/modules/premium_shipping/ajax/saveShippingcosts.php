<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();

$dispatchID = (empty($_REQUEST["feedID"])||!is_numeric($_REQUEST["feedID"])) ? 0 : (int) $_REQUEST["feedID"];
if(empty($dispatchID))
	die('FAIL');
$from = (empty($_REQUEST["from"])||!is_numeric($_REQUEST["from"])) ? "0" : (float) $_REQUEST["from"];
$value = (empty($_REQUEST["value"])||!is_numeric($_REQUEST["value"])) ? "0" : (float) $_REQUEST["value"];
$factor = (empty($_REQUEST["factor"])||!is_numeric($_REQUEST["factor"])) ? "0" : (float) $_REQUEST["factor"];

$sql = "
	DELETE FROM s_premium_shippingcosts WHERE `dispatchID`=$dispatchID AND `from`>=$from
";
$result = mysql_query($sql);
$sql = "
	INSERT INTO s_premium_shippingcosts  (dispatchID, `from`, `value`, `factor`)
	VALUES ($dispatchID, $from, $value, $factor)
";
$result = mysql_query($sql);
?>