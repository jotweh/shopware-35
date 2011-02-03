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
// from php manual page
$dir = "../../backend/modules/statistics/reports/";
$node = $_REQUEST['node'];

$nodes = array();

$charts = array(
	"Abgebrochene Warenk&ouml;rbe" => array("basket", "MSCombiDY2D","file",true,1,1,14),
	"Conversion Rate" => array("conversion", "MSCombiDY2D","file",true,1,2,7),
	"Umsatz/Besucher" => array("amount", "MSColumn3DLineDY","file",true,1,2,7),
	"Umsatz nach Kategorien" => array("amount_cat2", "Doughnut3D","folder",false,1,4,14),
	"Umsatz nach Kalenderwochen" => array("amount_week", "MSCombi2D","file",true,1,2,7),
	"Umsatz nach Monaten" => array("amount_month", "MSCombi2D","file",true,1,3,10),
	"Umsatz nach Referer" => array("referer_user", "","file",true,1,1,14),
	"Kunden nach Umsatz" => array("amount_user", "Bar2D","file",true,1,1,14),
	"L&auml;nder" => array("amount_user_country", "Pie2D","file",true,0,0,0),
	"Referer" => array("referer", "","file",true,1,1,0),//Bar2D
	"Google Keywords" => array("referer_google", "","file",true,1,1,0),
	"Suche" => array("search", "","file",true,1,1,7),
	"Article View/Sales" => array("article.views.sales", "","file",true,1,1,7),
	"Article Sales" => array("article.sales", "","file",true,1,1,7),
	"Article View" => array("article.views", "","file",true,1,1,7)
);
if($node==1)
{
	foreach ($charts as $name=>$chart)
		$nodes[] = array('text'=>$name, direct=>"../charts.php?chart={$chart[0]}&swf={$chart[1]}&table={$chart[4]}&dtyp={$chart[5]}&range={$chart[6]}",id=>$chart[0], leaf=>$chart[3], cls=>$chart[2]);
}
elseif ($node=="amount_cat2"||$node>1)
{
	if(!is_numeric($node))
		$node = 1;
	$getCategories = mysql_query("
	SELECT id, description, position, parent FROM s_categories WHERE parent=$node ORDER BY position, description
	");
	if (@mysql_num_rows($getCategories)){
		while ($category=mysql_fetch_assoc($getCategories)){
			$getCategories2 = mysql_query("
				SELECT id, description, position, parent FROM s_categories WHERE parent={$category["id"]}
			");
			if (@mysql_num_rows($getCategories2))
				$nodes[] = array('text'=>$category["description"], direct=>"../charts.php?chart=amount_cat2&swf=Doughnut3D&table=1&node={$category["id"]}&dtyp=4&range=14", id=>$category["id"], parentId=>'amount_cat2',leaf=>false, cls=>'folder');
		}
	}
}

echo $json->encode($nodes);
?>