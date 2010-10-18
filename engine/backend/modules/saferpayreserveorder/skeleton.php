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
		"title": "<?php echo "Saferpay Zahlungen"; ?>",
		"minwidth": "1050",
		"width": 1050,
		"minheight": "570",
		"height": 570,
		"content": "",
		"loader": "iframe",
		"url": "transactions.php",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Kunden#Reservierte_Zahlungen"
	}
}