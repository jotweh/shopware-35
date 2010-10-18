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
	$_REQUEST["node"] = 1;
}

$nodes = array();



// Deutsch links:gLeft;Deutsch unten:gBottom

$positions = $sCore->sCONFIG["sCMSPOSITIONS"];
$positions = explode(";",$positions);


if ($_REQUEST["node"]==1){
	foreach ($positions as $position){
		$position = explode(":",$position);
		if ($position[0] && $position[1]){
			$nodes[] = array('text'=>$position[0], 'id'=>$position[1],'leaf'=>false, 'iconcls'=>'');
		}
	}
}else {
	$getCategories = mysql_query("
	SELECT * FROM s_cms_static 
	WHERE grouping LIKE '%{$_REQUEST["node"]}%'
	ORDER BY position ASC
	");
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$grouping = array_flip(explode("|",$category["grouping"]));
			//print_r($grouping); die($_REQUEST["node"]);
			if (!isset($grouping[$_REQUEST["node"]])) continue;
			$category["name"] = utf8_encode($category["description"]);
			$nodes[] = array('text'=>$category["name"]." ({$category["id"]})", 'id'=>$category["id"],'leaf'=>true, 'iconcls'=>'');
			
		}
	}
}
echo $json->encode($nodes);
?>