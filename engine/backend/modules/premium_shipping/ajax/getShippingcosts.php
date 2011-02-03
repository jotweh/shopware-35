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

$feedID = (empty($_REQUEST["feedID"])||!is_numeric($_REQUEST["feedID"])) ? 0 : (int) $_REQUEST["feedID"];

$minChange = (empty($_REQUEST["minChange"])||!is_numeric($_REQUEST["minChange"])) ? 0 : (float) $_REQUEST["minChange"];

$nodes = array();
$sql = "
	SELECT * FROM s_premium_shippingcosts WHERE `dispatchID`=$feedID ORDER BY `from`
";
$result = mysql_query($sql);
if ($result&&mysql_num_rows($result)){
	for($i=0;$node = mysql_fetch_assoc($result);$i++)
	{
		if($i)
		{
			$nodes[$i-1]["to"] = $node["from"]-$minChange;
		}
		if(empty($node["to"]))
			$node["to"] = "";
		if(empty($node["value"]))
			$node["value"] = "";
		if(empty($node["factor"]))
			$node["factor"] = "";
		$nodes[$i] = $node;
	}
}
if(empty($nodes))
{
	$nodes[] = array(
		"from"=>(float) $_REQUEST["startValue"],
		"value"=>"",
		"factor"=>""
	);
}
echo  $json->encode(array("articles"=>array_values($nodes),"count"=>count($nodes)));
?>