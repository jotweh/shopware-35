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
$connectString = "?domain=".$sCore->sCONFIG["sHOST"]."&pairing=".$sCore->sCONFIG["sACCOUNTID"];
?>
{
	"init": {
		"title": "<?php echo $sLang["account"]["skeleton_your_shopware_account"] ?>",
		"width": 860,
		"height": 550,
		"minwidth": 860,
		"minheight": 500,
		"content": "",
		"loader": "iframe",
		"url": "start.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Shopware_Account"
	}
}
