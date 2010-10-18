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

require_once("../json.php");
$json = new Services_JSON();

$getCategories = mysql_query("
	SELECT id, description FROM s_core_tax ORDER BY `id` ASC ");

if (!$getCategories){
echo "FAIL";
	die();
}

while ($Category = mysql_fetch_assoc($getCategories)){
	$Category["name"] = htmlentities(($Category["description"]));
	$ret["taxes"][] = $Category;
}
echo $json->encode($ret);
exit;
return;
?>