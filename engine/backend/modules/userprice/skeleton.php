<?php
define('sAuthFile', 'sSUMMARY');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
?>
{
	"init": {
		"title": "Kundenspezifische Preise",
		"minwidth": "800",
		"minheight": "610",
		"width": 800,
		"height": 610,
		"content": "",
		"loader": "iframe",
		"url": "index.php",
		"help":""
	}	
}
