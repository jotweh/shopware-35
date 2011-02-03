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

$articleID = intval($_REQUEST['articleID']);

$nodes = array();

$getBundles = mysql_query("
SELECT * FROM s_articles_live WHERE `articleID` = '{$articleID}'
");

if (@mysql_num_rows($getBundles)){
	while ($bundle=mysql_fetch_assoc($getBundles)){
		// Check for leafs

		$id = $bundle["id"];
		$name = utf8_encode($bundle['name']);
		if(empty($bundle['active'])) $name.= " <i>(inaktiv)</i>";

		$nodes[] = array('text'=>$name,  'id'=>$id ,'leaf'=>true, 'iconcls'=>'');
	}
}
echo $json->encode($nodes);

?>