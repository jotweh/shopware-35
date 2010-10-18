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

// Fetch-Data from categories
$_REQUEST["node"] = intval($_REQUEST["node"]);

if (!$_REQUEST["node"]){
	$_REQUEST["node"] = 1;
}

$nodes = array();
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

if ($_POST["pagingID"]) $_POST["id"] = $_POST["pagingID"];
if (!$_POST["id"]) $_POST["id"] = 3;
if (!$_POST["limit"]) $_POST["limit"] = 25;

if ($_POST["sort"]=="supplier") $_POST["sort"] = "supplierName";

if (!$_POST["sort"] || $_POST["sort"]=="lastpost") $_POST["sort"] = "id";
if (!$_POST["dir"]) $_POST["dir"] = "DESC";

if ($_POST["search"]){
	
}

if(!empty($_REQUEST['filter_user'])){
	$filter_user_q = mysql_query("SELECT name FROM `s_core_auth` WHERE `id` = '{$_REQUEST['filter_user']}'");
	$fullname = mysql_result($filter_user_q, 0, 'name');
	$filter_user_where = "AND `value1` = '{$fullname}'";
}

$sql = "
SELECT *
FROM s_core_log
WHERE `type`='backend'
{$filter_user_where}
ORDER BY `{$_POST["sort"]}` {$_POST["dir"]}
";

//echo $sql;


//echo $sql;

$resultCountArticles = mysql_query($sql);
$nodes["totalCount"] = @mysql_num_rows($resultCountArticles);

$sql = "
SELECT *
FROM s_core_log
WHERE `type`='backend'
{$filter_user_where}
ORDER BY `{$_POST["sort"]}` {$_POST["dir"]} LIMIT {$_POST["start"]},{$_POST["limit"]}";
$getVotes = mysql_query($sql);

//echo $sql;

if (@mysql_num_rows($getVotes)){
	while ($vote=mysql_fetch_assoc($getVotes)){
		$vote["key"] = utf8_encode($vote["key"]);
		$vote["text"] = utf8_encode($vote["text"]);
		$vote["value1"] = utf8_encode($vote["value1"]);
		
		$nodes["votes"][] = $vote;
	}
}else {
	$nodes["votes"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>