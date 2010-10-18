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

if ($_REQUEST["id"]){
	$id = intval($_REQUEST["id"]);
	$getIm = mysql_query("
	SELECT status FROM s_core_im WHERE id = $id
	");
	$getStatus = unserialize(mysql_result($getIm,0,"status"));
	$getStatus[$_SESSION["sID"]] = true;
	$updateIm = mysql_query("
	UPDATE s_core_im SET status = '".serialize($getStatus)."' WHERE id = $id
	");
	
	
	
}

	
?>