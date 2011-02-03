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
$_GET["category"] = intval($_GET["category"]);

if (!$_GET["category"]) echo "false";
$checkCategory = mysql_query("
SELECT id FROM s_categories WHERE parent=".$_GET["category"]);

if (@mysql_num_rows($checkCategory)){
	echo "false";
}else {
	echo "true";
}

exit;
?>