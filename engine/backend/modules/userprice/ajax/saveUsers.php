<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
$pricegroupID = $_REQUEST['pricegroupID'];
if(!empty($_REQUEST['userIDs']))
{
	foreach ($_REQUEST['userIDs'] as &$userID)
	{
		$userID = (int) $userID;
	}
	$userIDs = implode(',',$_REQUEST['userIDs']);
	$sql = "UPDATE s_user SET pricegroupID=NULL WHERE pricegroupID=$pricegroupID AND id NOT IN ($userIDs)";
	mysql_query($sql);
	$sql = "UPDATE s_user SET pricegroupID=$pricegroupID WHERE id IN ($userIDs)";
	mysql_query($sql);
}
else
{
	$sql = "UPDATE s_user SET pricegroupID=NULL WHERE pricegroupID=$pricegroupID";
	mysql_query($sql);
}

?>