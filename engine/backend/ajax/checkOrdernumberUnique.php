<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
include("json.php");

if ($result!="SUCCESS"){
	echo("FAIL");
	die();
}

if (empty($_REQUEST["ordernumber"])){
	die("missing param ordernumber");
}else {
	$ordernumber = mysql_real_escape_string($_REQUEST["ordernumber"]);
	$ordernumber = trim($ordernumber);

	$own = trim($_REQUEST['own']);
	$liveown = trim($_REQUEST['liveown']);
	if(!empty($own))
	{
		$own = mysql_real_escape_string($own);
		$checkAdd = sprintf("AND `ordernumber` != '%s'", $own);
	}elseif(!empty($liveown))
	{
		$liveown = mysql_real_escape_string($liveown);
		$checkLiveAdd = sprintf("AND `ordernumber` != '%s'", $liveown);
	}

	$checkDetails = sprintf("
		SELECT *
		FROM `s_articles_details`
		WHERE `ordernumber` LIKE '%s'
	", $ordernumber);
	$checkDetailsQ = mysql_query($checkDetails);


	$checkGrpVal = sprintf("
		SELECT *
		FROM `s_articles_groups_value`
		WHERE `ordernumber` LIKE '%s'
	", $ordernumber);
	$checkGrpValQ = mysql_query($checkGrpVal);


	$checkBundle = sprintf("
		SELECT *
		FROM `s_articles_bundles`
		WHERE `ordernumber` LIKE '%s'
		%s
	", $ordernumber, $checkAdd);
	$checkBundleQ = mysql_query($checkBundle);


	$checkLive = sprintf("
		SELECT *
		FROM `s_articles_live`
		WHERE `ordernumber` LIKE '%s'
		%s
	", $ordernumber, $checkLiveAdd);
	$checkLiveQ = mysql_query($checkLive);

	if(mysql_num_rows($checkDetailsQ) != 0 || mysql_num_rows($checkGrpValQ) != 0 || mysql_num_rows($checkBundleQ) != 0 || mysql_num_rows($checkLiveQ) != 0)
		echo "FAIL";
	else
		echo "SUCCESS";
}
?>