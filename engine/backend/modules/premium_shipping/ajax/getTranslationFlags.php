<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
function sGetShopPath()
{
	global $sCore;
	$path = $_SERVER["SERVER_PORT"] == 443 ? "https" : "http";
	$path .= '://';
	$path .= $sCore->sCONFIG['sBASEPATH'].'/';
	return $path;
}
$sCore->sInitTranslations(1,"config_dispatch","true");

if(empty($_REQUEST["feedID"]))
	exit();

$feedID = (int)$_REQUEST["feedID"];

if(!empty($_REQUEST["field"]))
switch ($_REQUEST["field"]) {
	case "name":
		$field = "name";
		break;
	case "description":
		$field = "description";
		break;
	case "status_link":
		$field = "status_link";
		break;
	default:
		break;
}
if(!empty($field)&&!empty($feedID))
{
	$translation = $sCore->sBuildTranslation("dispatch_".$field,"dispatch_".$field,"1","config_dispatch",$feedID);
	$translation = str_replace(sConfigPath,sGetShopPath(),$translation);
	echo $translation;
}
?>