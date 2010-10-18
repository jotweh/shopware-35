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

if (is_numeric($_REQUEST["user"])){
	$id = intval($_REQUEST["user"]);
	$queryVote = mysql_query("
	DELETE FROM s_articles_supplier WHERE id = $id
	");
}
?>