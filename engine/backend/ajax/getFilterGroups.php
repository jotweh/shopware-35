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
?>
<?php
require_once("json.php");
$json = new Services_JSON();
?>
<?php
// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);
if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 0;
}

$nodes = array();

if ($_REQUEST["node"]){
	$getCategories = mysql_query("
	SELECT name,optionID,fo.id AS id FROM s_filter_relations AS re, s_filter_options AS fo WHERE re.groupID = {$_REQUEST["node"]} 
	AND re.optionID = fo.id
	ORDER BY position ASC
	");
	
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$nodes[] = array('text'=>utf8_encode($category["name"]), 'id'=>$_REQUEST["node"]."_".$category["id"],'leaf'=>true,'parentId'=>$_REQUEST["node"]);
		}
	}	
}else {
	$getCategories = mysql_query("
	SELECT * FROM s_filter ORDER BY name ASC
	");
	
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$nodes[] = array('text'=>utf8_encode($category["name"]), 'id'=>$category["id"],'leaf'=>false, 'cls'=>'folder','parentId'=>0);
		}
	}
}
echo $json->encode($nodes);

?>