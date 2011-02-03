<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("../../core/class/sTicketSystem.php");
include("json.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}
// Create sTicket instance
$sTicketSystem = new sTicketSystem();

$sort = $_REQUEST['sort'];
$dir = $_REQUEST['dir'];
$start = $_REQUEST['start'];
$limit = $_REQUEST['limit'];
$search = $_REQUEST['search'];

$aFilter = array();
$aFilter['filter_status'] = $_REQUEST['filter_status'];
$aFilter['filter_employee'] = $_REQUEST['filter_employee'];

$json = new Services_JSON();
echo $json->encode($sTicketSystem->getTicketSupportStore($sort, $dir, $start, $limit, $search, "", $aFilter));
?>