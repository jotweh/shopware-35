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

$minChange = (empty($_REQUEST["minChange"])||!is_numeric($_REQUEST["minChange"])) ? 0 : (float) $_REQUEST["minChange"];
$startValue = (empty($_REQUEST["startValue"])||!is_numeric($_REQUEST["startValue"])) ? 0 : (float) $_REQUEST["startValue"];
if(empty($_REQUEST['netto'])&&!empty($_REQUEST['tax']))
{
	$tax = (float) $_REQUEST["tax"];
}
else
{
	$tax = 0;
}

if(empty($_REQUEST['config']))
{
	$sql = "
		SELECT `from`, `price`, `pseudoprice`, `baseprice`, `percent`
		FROM s_articles_details d, s_articles_prices p
		WHERE d.ordernumber='".mysql_real_escape_string($_REQUEST['ordernumber'])."'
		AND p.articledetailsID=d.id
		AND p.pricegroup='".mysql_real_escape_string($_REQUEST['pricegroup'])."'
		ORDER BY `from`
	";
	$result = mysql_query($sql);
	if(!$result||!mysql_num_rows($result))
	{
		$sql = "
			SELECT `from`, `price`, `pseudoprice`, `baseprice`, `percent`
			FROM s_articles_details d, s_articles_prices p
			WHERE d.ordernumber='".mysql_real_escape_string($_REQUEST['ordernumber'])."'
			AND p.articledetailsID=d.id
			AND p.pricegroup='EK'
			ORDER BY `from`
		";
		$result = mysql_query($sql);
	}
}
else
{
	$sql = "
		SELECT 1 as `from`, gp.`price`, 0 as `pseudoprice`, 0 as `baseprice`, 0 as `percent`
		FROM s_articles_groups_value gv, s_articles_groups_prices gp
		WHERE gv.ordernumber='".mysql_real_escape_string($_REQUEST['ordernumber'])."'
		AND gp.valueID=gv.valueID
		AND ( gp.groupkey='".mysql_real_escape_string($_REQUEST['pricegroup'])."' OR gp.groupkey='EK' )
		ORDER BY gp.groupkey='EK'
		LIMIT 1
	";
	$result = mysql_query($sql);
}

$nodes = array();

if ($result&&mysql_num_rows($result)){
	for($i=0;$node = mysql_fetch_assoc($result);$i++)
	{
		if($i)
		{
			$nodes[$i-1]["to"] = $node["from"]-$minChange;
		}
		$node["price"] = round($node["price"]*(100+$tax)/100,2);
		if(empty($node["pseudoprice"])) $node["pseudoprice"] = '';
		if(empty($node["baseprice"])) $node["baseprice"] = '';
		if(empty($node["percent"])) $node["percent"] = '';
		$nodes[$i] = $node;
	}
}
if(empty($nodes)&&!empty($_REQUEST['ordernumber']))
{
	$nodes[] = array(
		"from"=>$startValue,
		"value"=>"",
		"factor"=>""
	);
}
echo  $json->encode(array("articles"=>array_values($nodes),"count"=>count($nodes)));
?>