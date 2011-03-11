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

$campaignid =  urlencode($_GET["template"]);
if (empty($campaignid)) $campaignid = "0";

?>
{
	"init": {
		"title": "<?php echo $sLang["templatepreview"]["skeleton_preview"] ?>",
		"minwidth": 800,
		"minheight": 580,
		"height": 580,
		"width": 800,
		"content": "",
		"loader": "extern",
		"url": "http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/shopware.php?sTpl=<?php echo $campaignid ?>"
	}
	
}
