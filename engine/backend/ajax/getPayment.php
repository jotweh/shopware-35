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
	SELECT id, name, description, active FROM s_core_paymentmeans  ORDER BY active DESC, name ASC ");

if (!$getCategories){
echo "FAIL";
	die();
}
$nodes = array();

while ($Category = mysql_fetch_assoc($getCategories))
{
	$icon = $Category["active"] ? '../../img/default/icons/tick.png' : '../../img/default/icons/cross.png';
	$Category["name"] = utf8_encode($Category["description"]);
	$nodes[] = array('text'=>$Category["name"]." ({$Category["id"]})", 'id'=>$Category["id"], icon => $icon,'leaf'=>true,);
}
echo $json->encode($nodes);
?>