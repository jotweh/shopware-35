<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "
	<html><title>Time-Out</title><head></head>
	<script language=\"javascript\">
	parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
if (!empty($_REQUEST["link"])){
	if (strpos($_REQUEST["link"], 'http://www.shopware.de/') !== 0) {
		exit;
	}
}
?>
{
	"init": {
		"title": "<?php echo $sLang["rss"]["skeleton_rss-reader"] ?>",
		"minwidth": "640",
		"minheight": "480",
		"width": "640",
		"height": "480",
		"content": "",
		"loader": "extern",
		"url": "<?php echo !empty($_REQUEST["link"]) ? $_REQUEST["link"] : "http://www.shopware.de/"; ?>"
	}
}