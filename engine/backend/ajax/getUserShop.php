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


$sql = "
SELECT shops.domainaliase FROM `s_user` AS usr
LEFT JOIN `s_core_multilanguage` AS shops ON usr.subshopID = shops.id 
WHERE usr.`id` = '{$_REQUEST["id"]}'
";

$getUser = mysql_query($sql);
//echo $sql;

if (@mysql_num_rows($getUser)){
	while ($user=mysql_fetch_assoc($getUser)){
		if (empty($user["domainaliase"])){
			$domain = "http://".$sCore->sCONFIG["sBASEPATH"]."/".$sCore->sCONFIG["sBASEFILE"];
		}else {
			$user["domainaliase"] = explode("\n",$user["domainaliase"]);
			$temp = str_replace($sCore->sCONFIG["sHOST"],$user["domainaliase"][0],$sCore->sCONFIG["sBASEPATH"]);
			$domain = "http://".$temp."/".$sCore->sCONFIG["sBASEFILE"];
		}
		echo $domain;
	}
}else {
	echo "FAIL";
}

?>