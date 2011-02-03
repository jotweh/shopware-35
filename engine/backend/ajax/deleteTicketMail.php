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

if (is_numeric($_REQUEST["id"])){
	$id = intval($_REQUEST["id"]);
	$queryVote = mysql_query("
	DELETE FROM s_ticket_support_mails WHERE id = $id
	");
}
?>