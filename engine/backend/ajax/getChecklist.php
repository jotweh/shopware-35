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

$data = array();

//Params
$start = intval($_REQUEST['start']);
if(empty($start)) $start=0;

$limit = intval($_REQUEST['limit']);
if(empty($limit)) $limit=25;


//diplay checked
if(empty($_REQUEST['displayChecked']))
{
	$ANDdisplayChecked = "AND `checked` != 1";
}

//=====================================================================================================
// Get active modules
//=====================================================================================================

$getModulesQ = mysql_query("
	SELECT `module` , `hash`
	FROM `s_core_licences`
");

$activeModsArray = array();
while ($module = mysql_fetch_array($getModulesQ)) {
	if ($sCore->sCheckLicense("","",$sCore->sLicenseData[$module['module']]))
		$activeModsArray[] = "'".$module['module']."'";
}
$activeMods = implode(", ", $activeModsArray);
$ANDmodules = "AND (  `module` = '' OR `module` IN ({$activeMods})   )";
//=====================================================================================================


//=====================================================================================================
// Get active paymentmeans
//=====================================================================================================

$getPaymentMeansQ = mysql_query("
	SELECT `name`
	FROM `s_core_paymentmeans`
	WHERE `active` = 1
");

$activePaymentMeansArray = array();
while ($paymentmean = mysql_fetch_array($getPaymentMeansQ)) {
		$activePaymentMeansArray[] = "'".$paymentmean['name']."'";
}
$activePaymentMeans = implode(", ", $activePaymentMeansArray);
$ANDpaymentmean = "AND (  `paymentmean` = '' OR `paymentmean` IN ({$activePaymentMeans})   )";
//=====================================================================================================

$getChecklistQ = mysql_query("
	SELECT *
	FROM `s_core_checklist`
	WHERE 1=1
	{$ANDdisplayChecked}
	{$ANDmodules}
	{$ANDpaymentmean}
	LIMIT {$start}, {$limit}
");


if(mysql_num_rows($getChecklistQ) != 0)
{
	while ($fetch = mysql_fetch_array($getChecklistQ)) {
		$fetch['area'] = htmlentities($fetch['area']);
		$fetch['subarea'] = htmlentities($fetch['subarea']);
		$fetch['option'] = htmlentities($fetch['option']);
		
		//modul dependent?
		if(!empty($fetch['module']))
		{
			if ($sCore->sCheckLicense("","",$sCore->sLicenseData[$fetch['module']]))
				$data[] = $fetch;
		}else{
			$data[] = $fetch;
		}
	}
}

$getChecklistTotalQ = mysql_query("
	SELECT COUNT(*) AS total
	FROM `s_core_checklist`
	WHERE 1=1
	{$ANDdisplayChecked}
	{$ANDmodules}
	{$ANDpaymentmean}
");
$total = mysql_result($getChecklistTotalQ, 0, 'total');

echo json_encode(array("data" =>$data, "total" =>$total));

?>