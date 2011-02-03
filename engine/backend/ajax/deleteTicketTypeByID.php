<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("../../core/class/sTicketSystem.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}
// Create sTicket instance
$sTicketSystem = new sTicketSystem();

$typeID = $_REQUEST['typeID'];
if(!$sTicketSystem->deleteTicketTypeByID($typeID)){
	echo "FAIL";
}
?>