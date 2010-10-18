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
	UPDATE s_articles_vote SET active = 1 WHERE id = $id
	");
}
?>