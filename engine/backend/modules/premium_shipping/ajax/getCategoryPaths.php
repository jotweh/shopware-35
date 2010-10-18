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

$feedID = (empty($_REQUEST["feedID"])||!is_numeric($_REQUEST["feedID"])) ? 1 : (int) $_REQUEST["feedID"];
		
function sGetCategoryPath ($start,$separator="/")
{
	$sql = "SELECT parent FROM s_categories WHERE id=".intval($start);
	$result = mysql_query($sql);
	if($result&&mysql_num_rows($result))
		$parent = (int) mysql_result($result,0,0);
	if(empty($parent)||$parent<2||$parent==$start) 
		return $separator.$parent;
	return sGetCategoryPath ($parent,$separator).$separator.$parent;
}

$nodes = array();
$sql = "
	SELECT c.id FROM s_categories c, s_premium_dispatch_categories ec WHERE c.id=ec.categoryID AND ec.dispatchID=$feedID
";

$result2 = mysql_query($sql);
if ($result2&&mysql_num_rows($result2))
{
	while ($category = mysql_fetch_row($result2))
	{
		$nodes[] = sGetCategoryPath((int) $category[0]);//."/".$category[0];
	}
}
echo $json->encode($nodes);
?>