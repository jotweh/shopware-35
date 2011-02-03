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


//Request
$delNode = $_REQUEST["id"];
$sql = sprintf("DELETE FROM s_campaigns_mailaddresses WHERE id = '%s' LIMIT 1", intval($delNode));
$queryVote = mysql_query($sql);
?>