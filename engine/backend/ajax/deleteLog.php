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
	$id = intval($_REQUEST["delete"]);
	$queryVote = mysql_query("
	DELETE FROM s_core_log WHERE id = $id
	");
}
?>