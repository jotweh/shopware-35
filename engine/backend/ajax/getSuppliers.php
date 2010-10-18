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

$getCategories = mysql_query("
SELECT s.id, s.name, s.img, COUNT(a.id) as count FROM s_articles_supplier s
LEFT JOIN s_articles AS a ON a.supplierID = s.id
GROUP BY s.id ORDER BY `name` ASC
");
if (!$getCategories){
echo "FAIL";
	die();
}
$nodes = array();

while ($Category = mysql_fetch_assoc($getCategories))
{
	$icon = $Category["img"] ? '../../img/default/icons4/image.png' : '';
	$Category["name"] = utf8_encode($Category["name"]." (".$Category["count"].")");
	//$nodes[] = array('text'=>$Category["name"], 'id'=>$Category["id"],'leaf'=>true, 'iconcls'=>'');
	$nodes[] = array('text'=>$Category["name"], 'id'=>$Category["id"],"count"=>$Category["count"], icon => $icon,'leaf'=>true,);
}
echo $json->encode($nodes);
?>