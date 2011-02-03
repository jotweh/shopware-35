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
	SELECT c.id, c.description, c.position, c.parent,c.blog,COUNT(c2.id) as count
	FROM s_categories c
	LEFT JOIN s_categories c2 ON c2.parent=c.id 
	WHERE c.parent={$_REQUEST["node"]}
	GROUP BY c.id
	ORDER BY c.position, c.description
";

$getCategories = mysql_query($sql);

if ($getCategories&&mysql_num_rows($getCategories)){
	while ($category=mysql_fetch_assoc($getCategories)){
		if (!empty($category["blog"])){
			$category["description"] = utf8_encode($category["description"]);
			if (!empty($category["count"])){
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder',"blog"=>$category["blog"]);
			}elseif(!empty($_REQUEST["move"])&&empty($category["article_count"])) {
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder',"blog"=>$category["blog"]);
			}else{
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'leaf'=>true,"blog"=>$category["blog"]);
			}
		}elseif(deepInvestigate($category["id"])) {
			if (!empty($category["count"])){
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder',"blog"=>$category["blog"]);
			}else{
				$nodes[] = array('text'=>$category["description"], 'id'=>$category["id"], 'parentId'=>$category["parent"], 'cls'=>'folder', 'leaf'=>true,"blog"=>$category["blog"]);
			}
		}
	}
}
echo $json->encode($nodes);
function deepInvestigate ($id){
	$getChilds = mysql_query("SELECT id,blog FROM s_categories WHERE parent = $id");
	while ($child = mysql_fetch_assoc($getChilds)){
		// Check if blog 
		if ($child["blog"]){
			return true;
		}else {
			$a =  deepInvestigate($child["id"]);
		}
		if ($a == true) return true; else continue;
	}
	return false;
}
?>