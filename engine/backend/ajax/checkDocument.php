<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../");
include("../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
include("json.php");

if ($result!="SUCCESS"){
	echo("FAIL");
	die();
}

if (empty($_POST["id"]) || empty($_POST["typ"])){
	die("");
}else {
	$documents = array();
	$ids = explode(";",$_POST["id"]);
	foreach ($ids as $id){
		$id = intval($id);
		$typ = intval($_POST["typ"]);
		$query = mysql_query("
		SELECT ID FROM s_order_documents WHERE orderID = $id AND type = '$typ'
		");
		if (@mysql_num_rows($query)){
			//echo "1";
		}else {
			// Document not exists
			$documents[] = $id;
		}
	}
	$json = new Services_JSON();
	if (!count($documents)){
		die("");
	}
	echo $json->encode($documents);
}
?>