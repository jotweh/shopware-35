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

$nodes = array();
if (empty($_POST["start"])) $_POST["start"] = "0";
if (empty($_POST["limit"])) $_POST["limit"] = 25;
$_POST["start"] = intval($_POST["start"]);
$_POST["limit"] = intval($_POST["limit"]);
$_POST["id"] = intval($_POST["id"]);

if (!empty($_POST["search"])){
	$_POST["search"] = mysql_real_escape_string($_POST["search"]);
	$where = "WHERE s_campaigns_mailaddresses.email LIKE '%{$_POST["search"]}%'";
}else {
	$where = "";
}

$sql = "SELECT s_campaigns_mailaddresses.*,sg.name,sc.description, s_user.id AS userID FROM s_campaigns_mailaddresses
LEFT JOIN s_user ON s_campaigns_mailaddresses.customer = 1 AND s_campaigns_mailaddresses.email = s_user.email
LEFT JOIN s_core_customergroups sc ON s_user.customergroup = sc.`groupkey`
LEFT JOIN s_campaigns_groups sg ON sg.id = s_campaigns_mailaddresses.groupID
$where
";

$query = mysql_query("
$sql
LIMIT {$_POST["start"]},{$_POST["limit"]}
");

$totalCount = @mysql_num_rows(mysql_query($sql));

if (@mysql_num_rows($query)){
	while ($email=mysql_fetch_assoc($query)){
		$user["email"] = utf8_encode($email["email"]);
		$user["groupname"] = utf8_encode($email["name"] ? $email["name"] : $email["description"]);
		$user["groupmode"] = $email["name"] ? 1 : 0;
		$user["lastletter"] = $email["lastmailing"];
		$user["userID"] = $email["userID"];
		$user["id"] = $email["id"];
		$nodes["emails"][] = $user;
	}
	$nodes["totalCount"] = $totalCount;
}else {
	$nodes["emails"] = array();
	$nodes["totalCount"] = 0;
}
echo $json->encode($nodes);

?>