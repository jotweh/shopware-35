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

//isocode
$iso = !empty($_REQUEST['iso']) ? strtolower($_REQUEST['iso']) : "de";

$nodes = array();


$getCategories = mysql_query("
		SELECT * FROM s_ticket_support_mails 
		WHERE `isocode` = '{$iso}'
		ORDER BY sys_dependent DESC, name DESC, description ASC
");
if (@mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){
		
		$category["name"] = utf8_encode($category["description"])." (".$category['id'].")";
		
		if($category["sys_dependent"] == '1')
		{
			$deleteable = false;
			$name = sprintf("[S] <i>%s</i>",$category["name"]);
		}else{
			$deleteable = true;
			$name = $category["name"];
		}
		
		$nodes[] = array('text'=>$name, 'id'=>$category["id"],'leaf'=>true, 'deleteable'=>$deleteable, 'iconcls'=>'');
	}
}

echo $json->encode($nodes);
?>