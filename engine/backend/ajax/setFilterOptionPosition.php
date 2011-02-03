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
// *****************
require_once("json.php");
$json = new Services_JSON();
if ($_REQUEST["node"]){
	$node = $json->decode($_REQUEST["node"]);
	foreach ($node as $key => $object){
		$position = $object->position;
		$id = $object->id;
		$id = explode("_",$id);
		$id[0] = intval($id[0]);
		$id[1] = intval($id[1]);
		if ($id[0] && $id[1]){
			// Search matching row
			$checkNode = mysql_query("
			SELECT id FROM s_filter_relations WHERE 
			groupID = {$id[0]} AND optionID = {$id[1]}  
			");
			if (@mysql_num_rows($checkNode)){
				$updateNode = mysql_query("
				UPDATE s_filter_relations SET position = $position
				WHERE groupID = {$id[0]} AND optionID = {$id[1]}  
				");
			}
		}
	}
}
?>