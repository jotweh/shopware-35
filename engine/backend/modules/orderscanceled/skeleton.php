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
// *****************
?>
{
	"init": {
		"title": "<?php echo $sLang["orderscanceled"]["skeleton_calceled_orders"] ?>",
		"minwidth": 1100,
		"minheight": 680,
		"height": 680,
		"width": 1100,
		"content": "",
		"loader": "iframe",
		"url": "orders.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Marketing#Abbruch-Analyse"
	}
	
}
