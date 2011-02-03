<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo $sLang["presettings"]["orderstatemail_fail"];
	die();
}
include_once('../../../vendor/adodb/adodb.inc.php');
include("../../../../config.php");
session_start(); # session variables required for monitoring

$conn = ADONewConnection("mysql");

$conn->Connect($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_DATABASE);
$perf =& NewPerfMonitor($conn);
$perf->UI($pollsecs=5);
?>