<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

$sql = "INSERT IGNORE INTO `s_core_config` (`name`, `value`) VALUES ('sPREMIUMSHIPPIUNGADOPTION', '1');";
mysql_query($sql);
if(!empty($_REQUEST['dispatch']))
{
	foreach ($_REQUEST['dispatch'] as $old => $new)
	{
		$sql = "UPDATE `s_order` SET `dispatchID` = ".intval($new)." WHERE `dispatchID` = ".intval($old);
		//echo "// $sql\n";
		mysql_query($sql);
	}
}
require_once("json.php");
$json = new Services_JSON();
$data = array(
	'msg' => '',
	'success'=> true
);
echo $json->encode($data);
?>