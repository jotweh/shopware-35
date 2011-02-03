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

$sArticleID = intval($_REQUEST['articleID']);
if(empty($sArticleID)) die;
$sBundleLook = "true"==$_REQUEST['sBundleLook'] ? 1 : 0;

$sqlQ = sprintf("UPDATE `s_articles` SET `crossbundlelook` = %s WHERE `id` = %s LIMIT 1 ;",
		$sBundleLook, $sArticleID);

if(mysql_query($sqlQ)) echo "SUCCESS";
?>