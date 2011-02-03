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
		"title": "<?php echo $sLang["categories"]["skeleton_Category_management"] ?>",
		"minwidth": "876",
		"minheight": "450",
		"content": "",
		"loader": "iframe2",
		"url": "categories.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Artikel#Kategorien_2"
	}
	
}
