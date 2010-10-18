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
SELECT * FROM s_core_auth
");

if(isset($_REQUEST['filter'])){
	$nodes[] = array('text'=>"Filter deaktivieren", 'id'=>-1,'leaf'=>true, 'iconcls'=>'');
}
if(isset($_REQUEST['ticketsystem'])){
	$nodes[] = array('text'=>"Keine Zuordnung", 'id'=>0,'leaf'=>true, 'iconcls'=>'');
}

if(isset($_REQUEST['transactions'])){
	$nodes[] = array('fullname'=>"Filter deaktivieren", 'id'=>0,'leaf'=>true, 'iconcls'=>'');
}

if (@mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){
		// Check for leafs
		
		$fullname = utf8_encode($category['name']);
		
		if (!$category["name"]) $category["name"] = $category["username"];
		$category["name"] = utf8_encode($category["name"]);
		
		
		
		if(isset($_REQUEST['ticketsystem'])){
			if(!preg_match("/^shopware/", $category["name"]) && !preg_match("/^Shopware/", $category["name"])){
				$nodes[] = array('text'=>$category["username"], 'fullname'=>$fullname, 'id'=>$category["id"],'leaf'=>true, 'iconcls'=>'');
			}
		}else{
			$nodes[] = array('text'=>$category["name"], 'fullname'=>$fullname, 'id'=>$category["id"],'leaf'=>true, 'iconcls'=>'');
		}
			
		
	}
}
echo $json->encode($nodes);

?>