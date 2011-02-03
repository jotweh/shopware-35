<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo "FAIL";
	die();
}
// *****************

require_once("json.php");
$json = new Services_JSON();

$dir = "../../../backend/modules/statistics/reports/";
$node = $_REQUEST['node'];

$nodes = array();

//$charts = $sLang["statistics"]["get_Statistics_array"];
// Get charts
$getCharts = mysql_query("
SELECT * FROM s_core_statistics ORDER BY position ASC
");

if($node==1)
{
	while ($chart=mysql_fetch_assoc($getCharts)){
		$nodes[] = array('text'=>$chart["name"],"chart"=>$chart["chart"],"description"=>nl2br(htmlentities($chart["description"],ENT_QUOTES)),"table"=>$chart["table"],"range"=>0,id=>$chart["file"], leaf=>$chart["leaf"] ? true : false, "cls"=>$chart["type"]);
	}
}
elseif ($node=="amount_cat2"||$node>1)
{
	if(!is_numeric($node))
		$node = 1;
	$getCategories = mysql_query("
	SELECT id, description, position, parent FROM s_categories WHERE parent=$node ORDER BY position, description
	");
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_array($getCategories)){
			$getCategories2 = mysql_query("
				SELECT id, description, position, parent FROM s_categories WHERE parent={$category["id"]}
			");
			if (@mysql_num_rows($getCategories2))
				$nodes[] = array('text'=>utf8_encode($category["description"]),"file"=>"amount_cat2","chart"=>"Doughnut3D", direct=>"../charts.php?chart=amount_cat2&swf=Doughnut3D&table=1&node={$category["id"]}&dtyp=4&range=14", id=>$category["id"], parentId=>'amount_cat2',leaf=>false, "table"=>$chart[4],"description"=>nl2br(htmlentities($chart[7],ENT_QUOTES)),cls=>'folder');
			else 
				$nodes[] = array('text'=>utf8_encode($category["description"]),"file"=>"amount_cat2","chart"=>"Doughnut3D", direct=>"../charts.php?chart=amount_cat2&swf=Doughnut3D&table=1&node={$category["id"]}&dtyp=4&range=14", id=>$category["id"], parentId=>'amount_cat2',leaf=>true, "table"=>$chart[4],"description"=>nl2br(htmlentities($chart[7],ENT_QUOTES)),cls=>'folder');
		}
	}
}

echo $json->encode($nodes);
?>