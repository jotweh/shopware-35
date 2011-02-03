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


//Request
$delNode = $_REQUEST["nodeId"];
if(substr($delNode, 0, 3) == "cms")
{
	$type = "cms";
	//cut prefix
	$strlen = strlen($delNode);
	$id = substr($delNode, 3, $strlen-3);	
}else{
	$type = "topic";
	$id = $delNode;
}
if ($_REQUEST["user"]){
	$id = $_REQUEST["user"];
	$type = "static";
}


if ($type == "cms"){
	$id = intval($id);
	$sql = sprintf("DELETE FROM s_cms_content WHERE id = '%s' LIMIT 1", $id);
	$queryVote = mysql_query($sql);
}elseif ($type == "topic"){
	$id = intval($id);
	
	$sql = sprintf("DELETE FROM s_cms_groups WHERE id = '%s' LIMIT 1", $id);
	$queryVote = mysql_query($sql);
	
	$sql = sprintf("DELETE FROM s_cms_content WHERE groupID = '%s'", $id);
	$queryVote = mysql_query($sql);
}elseif ($type=="static"){
	$id = intval($id);
	$sql = sprintf("DELETE FROM s_cms_static WHERE id = '%s' LIMIT 1", $id);
	$queryVote = mysql_query($sql);
}

if(isset($_REQUEST['debug']))
{
	echo "nodeId: ".$delNode;
	echo "<br>";
	echo "type: ".$type;
	echo "<br>";
	echo "id: ".$id;
	echo "<br>";
	echo "SQL-DELETE:<br>";
	echo $sql;
}
?>