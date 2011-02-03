<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

require_once("json.php");
$json = new Services_JSON();

$node = (empty($_REQUEST["node"])||!is_numeric($_REQUEST["node"])) ? 1 : (int) $_REQUEST["node"];
$feedID = (empty($_REQUEST["feedID"])||!is_numeric($_REQUEST["feedID"])) ? 0 : (int) $_REQUEST["feedID"];
$nodes = array();
$sql = "
	SELECT c.id, c.description as text, c.parent as parentId, IF(COUNT(c2.id)>0,0,1) as leaf, IF(ec.categoryID IS NULL,0,1) as checked
	FROM s_categories c
	LEFT JOIN s_categories c2 ON c2.parent=c.id
	LEFT JOIN s_premium_dispatch_categories ec ON ec.categoryID=c.id AND ec.dispatchID=$feedID
	WHERE c.parent=$node
	GROUP BY c.id ORDER BY c.position, c.description
";
$getCategories = mysql_query($sql);

if ($getCategories&&mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){

		$category["text"] = utf8_encode($category["text"]);
		$category["leaf"] = !empty($category["leaf"]);
		$category["checked"] = !empty($category["checked"]);
		$nodes[] = $category;
	}
}
echo $json->encode($nodes);
?>