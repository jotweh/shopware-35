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
		"title": "Ihre Startseite",
		"minwidth": "880",
		"minheight": "580",
		"content": "",
		"loader": "iframe",
		"url": "start.php"
	}

}
