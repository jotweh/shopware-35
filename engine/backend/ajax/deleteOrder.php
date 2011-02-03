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

if (is_numeric($_REQUEST["delete"])){
	$_GET["delete"] = intval($_REQUEST["delete"]);
	
	// Delete order
	$queryDelete = mysql_query("
	DELETE FROM s_order WHERE id = {$_GET["delete"]}
	");
	$queryDelete = mysql_query("
	DELETE FROM s_order_details WHERE orderID = {$_GET["delete"]}
	");
	$queryDelete = mysql_query("
	DELETE FROM s_order_documents WHERE orderID = {$_GET["delete"]}
	");
	
	
}
?>