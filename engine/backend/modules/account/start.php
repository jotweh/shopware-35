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
$connectString = "?domain=".$sCore->sCONFIG["sHOST"]."&pairing=".$sCore->sCONFIG["sACCOUNTID"];
header("Location: https://support.shopware2.de/account2/index.php$connectString");
?>