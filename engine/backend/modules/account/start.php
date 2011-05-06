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

$accountUrl = 'https://account.shopware.de/register.php'
	. '?domain=' .urlencode($sCore->sCONFIG['sHOST'])
	. '&pairing=' .urlencode($sCore->sCONFIG['sACCOUNTID']);

header('Location: ' . $accountUrl);