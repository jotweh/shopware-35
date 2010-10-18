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
	
if(isset($_REQUEST['json']))
{
	$json = new Services_JSON();
	$_REQUEST = $json->decode($_REQUEST['json']);
}

if(isset($_REQUEST['sidebar']))
{
	$_REQUEST['sidebar'] = (int) $_REQUEST['sidebar'];
	$sql = "UPDATE `s_core_auth` SET `sidebar` = '{$_REQUEST['sidebar']}' WHERE username='".$_SESSION['sUsername']."'";
	$result = mysql_query($sql);
	if (!$result)
		echo "FAIL";
	else 
		echo "SUCCESS";
}
if(!empty($_REQUEST['window'])&&!empty($_REQUEST['height'])&&!empty($_REQUEST['width']))
{
	$height = (int) $_REQUEST['height'];
	$width = (int) $_REQUEST['width'];
	$screenwidth = (int) $_REQUEST['screenwidth'];
	$window = (string) $_REQUEST['window'];
	$username = mysql_real_escape_string($_SESSION['sUsername']);	
	$_SESSION["sWindow_Size"][$window][$screenwidth] = array('height'=>$height,'width'=>$width);
	
	$window_size = mysql_real_escape_string(serialize($_SESSION["sWindow_Size"]));
	
	$sql = "UPDATE `s_core_auth` SET `window_size` = '$window_size' WHERE username='$username'";
	$result = mysql_query($sql);
	if (!$result)
		echo "FAIL";
	else 
		echo "SUCCESS";
}
elseif(!empty($_REQUEST['window']))
{
	
	$username = mysql_real_escape_string($_SESSION['sUsername']);
	$sql = "SELECT window_size FROM s_core_auth  WHERE username='$username'";
	$result = mysql_query($sql);
	if (!$result){
		die("FAIL");
	}
	$window_size = mysql_result($result,0,"window_size");
	$window_size = unserialize($window_size);
	if(isset($window_size[$_REQUEST['window']][$_REQUEST["screenwidth"]]))
	{
		$json = new Services_JSON();
		echo $json->encode($window_size[$_REQUEST['window']][$_REQUEST["screenwidth"]]);
	}
	else 
	{
		echo 'FAIL';
	}
}
/*
require_once("json.php");
$json = new Services_JSON();

$sql = "SELECT sidebar FROM s_core_auth  WHERE username='".$_SESSION['sUsername']."'";

$getArticles = mysql_query($sql);

if (!$getArticles){
echo "FAIL";
	die();
}

while ($Article = mysql_fetch_assoc($getArticles))
	$ret[] = $Article;
	
echo $json->encode($ret);
exit;
return;
*/
?>