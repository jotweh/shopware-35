<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
define('sAuthFile', 'sGUI');
define('sConfigPath','../../../../../');
include('../../../../backend/php/check.php');
$result = new checkLogin();
$result = $result->checkUser();
if ($result!='SUCCESS'){
	die();
}


require_once('../../../../backend/ajax/json.php');
$json = new Services_JSON();

$_REQUEST["node"] = intval($_REQUEST["node"]);
if (empty($_REQUEST["node"])){
	$_REQUEST["node"] = 1;
}

$nodes = array();
$sql = "
	SELECT c.id, c.description, c.position, c.parent, COUNT(c2.id) as count,
	(
		SELECT categoryID
		FROM s_articles_categories
		WHERE categoryID = c.id
		LIMIT 1
	) as article_count 
	FROM s_categories c
	LEFT JOIN s_categories c2 ON c2.parent=c.id 
	WHERE c.parent={$_REQUEST["node"]}
	GROUP BY c.id
	ORDER BY c.position, c.description
";
#LEFT JOIN s_articles_categories ac
#ON ac.categoryID=c.id
#
#echo $sql;
$getCategories = mysql_query($sql);

if ($getCategories&&mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){

		if(function_exists('mb_convert_encoding')) {
			$category["description"] = mb_convert_encoding($category["description"], 'UTF-8', 'HTML-ENTITIES');
		} else {
			$category["description"] = utf8_encode($category["description"]);
		}
		$category["description"] = html_entity_decode($category["description"]);
		
		if (!empty($category["count"])){
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'child'=>false);
		}elseif(!empty($_REQUEST["move"])&&empty($category["article_count"])) {
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'child'=>true);
		}else{
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'leaf'=>true, 'child'=>true);
		}
	}
}
echo $json->encode($nodes);

?>