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

if ($_POST["search"]){	// r302
	$_POST["node"] = 0;
	$_POST["search"] = mysql_real_escape_string($_POST["search"]);
}

if($_POST["node"] == '0')
{
	if (empty($_POST["search"])){
		$getCategories = mysql_query("
		SELECT * FROM s_core_config_text_groups 
		ORDER BY description ASC
		");
		if (@mysql_num_rows($getCategories)){
			while ($category=mysql_fetch_assoc($getCategories)){
				$category["description"] = utf8_encode($category["description"]);
				$nodes[] = array('text'=>$category["description"], 'id'=>'group_'.$category["groupID"],'leaf'=>false, 'iconcls'=>'');
				
			}
		}
	}else {
		$sql = "
		SELECT * FROM s_core_config_text
		WHERE name LIKE '%{$_POST["search"]}%' OR value LIKE '%{$_POST["search"]}%'
		ORDER BY description ASC
		";
		
		// Anders sprachige Textbausteine ZUSÄTZLICH auslesen
		$getSnippets = mysql_query("
		SELECT objectdata FROM s_core_translations WHERE objecttype = 'config_snippets'
		");
		while ($snippet = mysql_fetch_array($getSnippets)){
			$obj = unserialize($snippet["objectdata"]);
			
			foreach ($obj as $key => $value){
				
				if (preg_match("/{$_POST["search"]}/Uis",$value["value"])){
					
					$nodes[] = array('text'=>utf8_encode($value["value"]), 'id'=>$key,'leaf'=>true, 'iconcls'=>'');
				}
			}
		}
		
		$getCategories = mysql_query($sql);
		if (@mysql_num_rows($getCategories)){
			while ($category=mysql_fetch_assoc($getCategories)){
				$category["description"] = utf8_encode($category["name"]);
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"],'leaf'=>true, 'iconcls'=>'');
				
			}
		}
	}
}else{
	$conf = explode("_", $_POST["node"]);
	if($conf[0] == "group")
	{
		$getCategories = mysql_query("
		SELECT * FROM s_core_config_text 
		WHERE `group` = {$conf[1]} ORDER BY `description`
		");
		if (@mysql_num_rows($getCategories)){
			while ($category=mysql_fetch_assoc($getCategories)){
				if(!empty($category["description"]))
				{
					$text = $category["description"];
				}else{
					$text = $category["name"];
				}
				
				$text = utf8_encode($text);
				$nodes[] = array('text'=>$text, 'id'=>$category["id"],'leaf'=>true, 'iconcls'=>'');
				
			}
		}
	}
}
echo $json->encode($nodes);
?>