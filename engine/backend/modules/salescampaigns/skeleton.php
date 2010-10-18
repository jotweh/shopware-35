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
		"title": "<?php echo $sLang["salescampaigns"]["skeleton_Actions"] ?>",
		"width": "800",
		"height": "600",
		"minwidth": "800",
		"minheight": "600",
		"content": "",
		"loader": "iframe",
		"url": "campaigns.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Marketing#Aktionsmodul"
	}
	
}
