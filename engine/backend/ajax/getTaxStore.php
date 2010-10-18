<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
include("json.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die("FAIL");
}



$sql = "SELECT * FROM `s_core_tax`";
$result = mysql_query($sql);

while ($entry = mysql_fetch_assoc($result))
{
	$data['id'] = $entry['id'];
	$data['value'] = $entry['description'];
	$ret[] = $data;
}

$json = new Services_JSON();
echo $json->encode($ret);
?>