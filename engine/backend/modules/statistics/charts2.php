<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo "FAIL";
	exit();
}


if(isset($_REQUEST['r']))
{
	$vars = explode("|",$_REQUEST['r']);
	if(isset($vars[0]))
		$_REQUEST['chart'] = $vars[0];
	if(isset($vars[1]))
		$_REQUEST['date'] = $vars[1];
	if(isset($vars[2]))
		$_REQUEST['date2'] = $vars[2];
	if(isset($vars[3]))
		if (empty($_REQUEST["node"])) $_REQUEST['node'] = $vars[3];
	if(isset($vars[4]))
		$_REQUEST['range'] = $vars[4];
	if(isset($vars[5]) && $_REQUEST["group"]!="1")
		$_REQUEST['group'] = $vars[5];
	if(isset($vars[6]))
		$_REQUEST['tax'] = $vars[6];
}

if ($_REQUEST["testReferer"]) $_REQUEST["node"] = $_REQUEST["testReferer"];

if(file_exists("data/{$_REQUEST['chart']}.php")&&strpos($_REQUEST['chart'], '/') === false)
{
	include("data/{$_REQUEST['chart']}.php");
}
?>