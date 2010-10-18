<?php
/*
SHOPWARE 3.5.0 - Engine
(c) 2010/2011, shopware AG
*/

// AUTHENTIFICATION
// ====================================
define('sAuthFile', 'sGUI');
include("backend/php/check.php");
$result = new checkLogin();
if ($result->checkUser()!="SUCCESS"){
	
	header("location: auth.php");
	die();
} elseif (strpos($_SERVER['HTTP_USER_AGENT'], "Safari") !== false) {
	if(empty($_SESSION["reload"]))
	{
		header("location: auth.php");
		die();
	}
	else 
	{
		unset($_SESSION["reload"]);
	}
}
// ====================================
//\AUTHENTIFICATION 

if($sCore->sCONFIG["sVERSION"]=='3.0.5' || $sCore->sCONFIG["sVERSION"] == "3.0.5.1")
{
	$sql = 'UPDATE s_core_config SET value = \'3.5.0\' WHERE name=\'sVERSION\'';
	$update = mysql_query($sql);
	$sCore->sCONFIG["sVERSION"] = '3.5.0';
}


$path = $_SERVER["HTTPS"] ? "https" : "http";
$path .= "://";
$path .= $sCore->sCONFIG["sBASEPATH"]."/backend/index";

header("location: $path");
exit;
?>