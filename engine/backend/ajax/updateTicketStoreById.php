<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}
include("../../core/class/sTicketSystem.php");

// Create sTicket instance
$sTicketSystem = new sTicketSystem();

$aUpdates = array();
if(!empty($_REQUEST['statusID'])){ $aUpdates['statusID'] = $_REQUEST['statusID']; }
if(isset($_REQUEST['employeeID'])){ $aUpdates['employeeID'] = $_REQUEST['employeeID']; }

if(!$sTicketSystem->updateTicketDataById($_REQUEST['id'], $aUpdates))
{
	echo "FAIL";
}
?>