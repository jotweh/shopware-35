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


$message = $_GET["msg"];
$time = date("Y-m-d H:i:s");
$user = $_SESSION["sName"];
$module = $_GET["mod"];

$message = utf8_decode(mysql_real_escape_string($message));
$user = mysql_real_escape_string($user);
$module = utf8_decode(mysql_real_escape_string($module));

if ($message && $user && $module){
	// Insert Log
	$sql = "
	INSERT INTO s_core_log (`type`,`key`,`text`,datum,value1)
	VALUES ('backend','$module','$message','$time','$user')
	";
	
	$insertLog = mysql_query($sql);
}

?>