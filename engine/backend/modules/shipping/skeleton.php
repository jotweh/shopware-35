<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	die();
}
if (!empty($sCore->sCONFIG['sPREMIUMSHIPPIUNG']))
{
	$url = "../premium_shipping/index.php";
}
else
{
	$url = "shipping.php";
}
?>
{
	"init": {
		"title": "<?php echo $sLang["shipping"]["skeleton_forwarding_expenses"] ?>",
		"minwidth": "800",
		"minheight": "600",
		"width": "800",
		"height": "600",
		"content": "",
		"loader": "iframe",
		"url": "<?php echo $url;?>",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Einstellungen#Versandkosten"
	}
}
