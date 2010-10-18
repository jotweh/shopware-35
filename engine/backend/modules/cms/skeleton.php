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
		"title": "<?php echo $sLang["cms"]["skeleton_content_managment"] ?>",
		"width": 860,
		"height": 550,
		"minwidth": 860,
		"minheight": 500,
		"content": "",
		"loader": "iframe",
		"url": "cms2.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Inhalte#Feeds"
	}
	
}
