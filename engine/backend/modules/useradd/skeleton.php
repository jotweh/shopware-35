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
		"title": "<?php echo utf8_encode($sLang["useradd"]["window_title_add_user"]) ?>",
		"minwidth": "800",
		"minheight": "600",
		"content": "",
		"loader": "iframe",
		"url": "main.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Kunden#Anlegen"
	}	
}
