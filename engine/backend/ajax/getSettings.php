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

require_once("json.php");
$json = new Services_JSON();

$_REQUEST["node"] = intval($_REQUEST["node"]);
if (!$_REQUEST["node"]){
	$_REQUEST["node"] = "0";
}

$queryLog = mysql_query("
SELECT value FROM s_core_config WHERE name = 'sADODB_LOG'
");
$queryLog = mysql_result($queryLog,0,"value");


$sql = "SELECT id, name, file, action FROM s_core_config_groups WHERE parent={$_REQUEST["node"]} ORDER BY position ASC";
//echo $sql;
$getCategories = mysql_query($sql);

while ($category=mysql_fetch_array($getCategories)){
	// Check for leafs
		$getCategoryLeafs = mysql_query("
		SELECT id FROM s_core_config_groups WHERE parent={$category["id"]} LIMIT 1
		");
		
		if ($category["file"]=="perf.php" && empty($queryLog)) continue;
		$category["name"] = utf8_encode($category["name"]);
		if (preg_match("/\.\.\//",$category["file"])){
			$category["file"] = "../".$category["file"];
		}
		$category["file"] = str_replace("../../core/php","../../backend/php",$category["file"]);
		if (@mysql_num_rows($getCategoryLeafs)){
			$nodes[] = array('text'=>$category["name"],'file'=>$category["file"], 'action'=>$category["action"],id=>$category["id"], parentId=>$category["parent"], cls=>'folder');
		}else {
			$nodes[] = array('text'=>$category["name"],'file'=>$category["file"], 'action'=>$category["action"],id=>$category["id"], parentId=>0,leaf=>true, cls=>'folder');
		}
}




	
echo $json->encode($nodes);
exit;
?>