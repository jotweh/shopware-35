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
	$node = $node->id;
	if ($node){
		$node = explode("_",$node);
		if ($node[0] && $node[1]){
			$parent = intval($node[0]);
			$node = intval($node[1]);
			
			// Check if node was already assigned
			$checkNode = mysql_query("
			SELECT id FROM s_filter_relations WHERE 
			groupID = $parent AND optionID = $node  
			");
			if (@mysql_num_rows($checkNode)){
				exit;
			}else {
				// Insert node
				$insertNode = mysql_query("
				INSERT INTO s_filter_relations (groupID,optionID)
				VALUES ($parent,$node)
				");
			}
		}
	}
}


?>