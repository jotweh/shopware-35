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
$_POST["nodes"] = stripslashes($_POST["nodes"]);

$data = $json->decode($_POST["nodes"]);
$group = explode(":",$_POST["group"]);
$group = $group[0];

if (count($data)){
	foreach ($data as $row){
		
		if ($row->position && $row->id){
			//echo $row->position."->".$row->id;
			$position = intval($row->position);
			$id = intval($row->id);
			
			//echo $position.">".$id.">$group<br />";
			switch ($group){
				case "CAMPAIGNS":
					$update = mysql_query("
					UPDATE s_emarketing_promotion_main
					SET position=$position WHERE id=$id
					");
					break;
				case "CAMPAIGN":
					// Container
					$update = mysql_query("
					UPDATE s_emarketing_promotion_containers
					SET position=$position WHERE id=$id
					");
					break;
				case "ARTICLES":
					$update = mysql_query("
					UPDATE s_emarketing_promotion_articles
					SET position=$position WHERE id=$id
					");
					break;
				case "LINKS":
					$update = mysql_query("
					UPDATE s_emarketing_promotion_links
					SET position=$position WHERE id=$id
					");
					break;
			}
			/*$updateCategory = mysql_query("
			UPDATE s_categories SET position=$position WHERE id=$id
			");*/
		}
	}
}



?>