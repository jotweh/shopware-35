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

if(empty($_REQUEST['checklistItemId'])) die("missing param: checklistItemId");
$checklistItemId = intval($_REQUEST['checklistItemId']);
$checked = intval($_REQUEST['checked']);

$upd = "UPDATE `s_core_checklist` 
		SET `checked` = '{$checked}' 
		WHERE `id` = '{$checklistItemId}' 
		LIMIT 1";
if(mysql_query($upd))
{
	echo "SUCCESS";
}else{
	echo "FAIL";
}
?>