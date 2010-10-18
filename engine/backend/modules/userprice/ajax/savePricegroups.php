<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}

$upset = array();

$upset[] = "active=".(empty($_REQUEST["active"]) ? 0 : 1);
$upset[] = "netto=".(empty($_REQUEST["netto"]) ? 0 : 1);

$_REQUEST["name"] = str_replace("\xe2\x82\xac","&euro;",$_REQUEST["name"]);
$upset[] = "name=".((empty($_REQUEST["name"])) ? "''" : "'".utf8_decode(mysql_real_escape_string(trim($_REQUEST["name"])))."'");

$upset = implode(",",$upset);
if(!empty($_REQUEST["id"])&&is_numeric($_REQUEST["id"]))
{
	$id = (int) $_REQUEST["id"];
	$sql = "UPDATE s_core_customerpricegroups SET $upset WHERE id=$id";
	mysql_query($sql);
}
else
{
	$sql = "REPLACE INTO s_core_customerpricegroups SET $upset";
	mysql_query($sql);
	$id = mysql_insert_id();
}
?>