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
$getCategories = mysql_query($sql);

if ($getCategories&&mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){

		$category["description"] = utf8_encode(html_entity_decode($category["description"]));
		//Add CategoryID
		//$category["description"] = sprintf("%s [%s]", $category["description"], $category["id"]);
		
		if (!empty($category["count"])){
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder');
		}elseif(!empty($_REQUEST["move"])&&empty($category["article_count"])) {
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder');
		}else{
			$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'leaf'=>true);
		}
	}
}
echo $json->encode($nodes);

?>