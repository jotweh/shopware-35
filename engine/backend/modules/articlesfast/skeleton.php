<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	//echo $result;
	//header("location: auth.php");
	die();
}
?>
{
	"init": {
		"title": "&Uuml;bersicht",
		"minwidth": "1100",
		"minheight": "620",
		"content": "",
		"loader": "iframe",
		"help":"40",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Artikel#.C3.9Cbersicht",
		"url": "fast.php"
	}
	
}
