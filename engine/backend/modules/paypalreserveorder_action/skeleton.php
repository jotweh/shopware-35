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
		"title": "<?php echo $sLang["paypalreserveorder"]["action_skeleton_reserved_booking"] ?> <?php echo $_REQUEST['json']?>",
		"minwidth": "450",
		"width": 450,
		"minheight": "570",
		"height": 570,
		"content": "",
		"loader": "iframe",
		"url": "transactions.php?ordernr=<?php echo $_REQUEST['ordernr']?>"
	}
	
}