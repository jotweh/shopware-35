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
		"title": "Blog",
		"minwidth": "1100",
		"minheight": "620",
		"content": "",
		"loader": "iframe",
		"help":"40",
		"help":"",
		"url": "blog.php"
	}
	
}
