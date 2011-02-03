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
	$_GET["delete"] = $_REQUEST["delete"];
	$sCore->sDeleteArticle($_REQUEST["delete"]);
}
?>