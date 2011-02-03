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
		"title": "Artikel &Uuml;bersicht",
		"minwidth": "1030",
		"minheight": "620",
		"content": "",
		"loader": "iframe",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Artikel#.C3.9Cbersicht",
		"url": "summary.php"
	}
	
}
