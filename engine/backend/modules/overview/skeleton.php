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
		"title": "<?php echo $sLang["overview"]["skeleton_Evaluation_Overview"] ?>",
		"minwidth": 1100,
		"minheight": 650,
		"width": 1100,
		"height": 650,
		"content": "",
		"loader": "iframe",
		"url": "overview.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Marketing#.C3.9Cbersicht"
	}
	
}
