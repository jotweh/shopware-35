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
$sLives = array();
$ordernumber = mysql_real_escape_string($_REQUEST['ordernumber']);

$sql = sprintf("
	SELECT 
	lv.id,
	lv.name
	FROM `s_articles_live` AS lv
	LEFT JOIN `s_articles_details` AS ad
	ON ad.articleID = lv.articleID AND ad.ordernumber = '%s'
	LEFT JOIN `s_articles_groups_value` AS agv
	ON agv.articleID = lv.articleID AND agv.ordernumber = '%s'
	WHERE (agv.ordernumber IS NOT NULL
	OR ad.ordernumber IS NOT NULL)
	AND lv.active=1
	
", $ordernumber, $ordernumber);

$getLives = mysql_query($sql);

$total = mysql_num_rows($getLives);
if (@mysql_num_rows($getLives)){
	while ($live=mysql_fetch_assoc($getLives)){
		// Check for leafs

		$id = $live["id"];
		$name = utf8_encode($live['name']);

		$sLives[] = array('id'=>$id,  'name'=>$name);
	}
}
echo $json->encode(array('data' => $sLives, 'total' => $total));

?>