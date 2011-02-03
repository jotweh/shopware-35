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
$_REQUEST["node"] = addslashes($_REQUEST["node"]);
if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 0;
}

$nodes = array();



// Deutsch links:gLeft;Deutsch unten:gBottom

$positions = $sCore->sCONFIG["sCMSPOSITIONS"];
$positions = explode(";",$positions);


if ($_REQUEST["node"]==0){
	$getCategories = mysql_query("
	SELECT * FROM s_cms_groups 
	ORDER BY position ASC
	");
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$category["name"] = utf8_encode($category["description"]);
			$nodes[] = array('text'=>$category["name"], 'id'=>$category["id"],'leaf'=>false, 'iconcls'=>'');
			
		}
	}
}else {
	$getCategories = mysql_query("
	SELECT * FROM s_cms_content 
	WHERE groupID = {$_REQUEST["node"]}
	ORDER BY id DESC
	");
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$category["name"] = utf8_encode($category["description"]);
			$nodes[] = array('text'=>$category["name"], 'id'=>'cms'.$category["id"],'leaf'=>true, 'iconcls'=>'');
			
		}
	}
}
echo $json->encode($nodes);
?>