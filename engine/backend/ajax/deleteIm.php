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
$delNode = $_REQUEST["delete"];
$sql = sprintf("DELETE FROM s_core_im WHERE id = '%s' LIMIT 1", intval($delNode));
$queryVote = mysql_query($sql);
?>