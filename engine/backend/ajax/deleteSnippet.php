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

//Request
$delNode = $_REQUEST["nodeId"];
$conf = explode("_", $delNode);
if($conf[0] == "group")
{
	$type = "group";
	//cut prefix
	$strlen = strlen($delNode);
	$id = $conf[1];	
}else{
	$type = "item";
	$id = $delNode;
}

if ($type == "item"){
	$id = intval($id);
	$sql = sprintf("DELETE FROM s_core_config_text WHERE id = '%s' LIMIT 1", $id);
	$queryVote = mysql_query($sql);
}elseif ($type == "group"){
	$id = intval($id);
	
	$sql = sprintf("DELETE FROM s_core_config_text_groups WHERE `groupID` = '%s' LIMIT 1", $id);
	$queryVote = mysql_query($sql);
	
	$sql = sprintf("DELETE FROM s_core_config_text WHERE `group` = '%s'", $id);
	$queryVote = mysql_query($sql);
}
?>