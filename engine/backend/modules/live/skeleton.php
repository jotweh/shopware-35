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
		"title": "<?php echo $sLang["live"]["skeleton_live_view"] ?>",
		"minwidth": "640",
		"minheight": "480",
		"content": "",
		"loader": "extern",
		"url": "http://<?php echo $sCore->sCONFIG['sBASEPATH']?>"
	}
	
}
