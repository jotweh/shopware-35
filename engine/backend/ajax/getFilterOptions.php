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
	$_REQUEST["node"] = 1;
}

$nodes = array();

$getCategories = mysql_query("
SELECT * FROM s_filter_options ORDER BY name ASC
");

if (@mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){
		$nodes[] = array('text'=>utf8_encode($category["name"]), 'id'=>$category["id"],'leaf'=>true, 'cls'=>'folder');
	}
}
echo $json->encode($nodes);

?>