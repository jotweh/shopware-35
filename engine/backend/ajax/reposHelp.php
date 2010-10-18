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
$_POST["nodes"] = stripslashes($_POST["nodes"]);
$data = $json->decode($_POST["nodes"]);

if (count($data)){
	foreach ($data as $row){
		
		if ($row->position && $row->id){
			//echo $row->position."->".$row->id;
			$position = intval($row->position);
			$id = intval($row->id);
			
			$updateCategory = mysql_query("
			UPDATE s_help SET position=$position WHERE id=$id
			");
		}
	}
}



?>