<?php
/*
Sofort-Überweisung Schnittstelle
Version 1.0
(c)2008, Hamann-Media GmbH
*/
$path = "../";
include("sofort.class.php");
$payment = new sofortPayment("/dev/null","../");
$payment->initUser();
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;
$value = $payment->getAmount();
round(&$value,2);

//2do >> payment.class metode
$http = !empty($_SERVER["HTTPS"]) ? "https://" : "http://"; 

$basepath = $payment->sSYSTEM->sCONFIG['sBASEPATH'];
$tplpath = $payment->sSYSTEM->sCONFIG['sTEMPLATEPATH'];
$URL = $http.$basepath."/".$tplpath;

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/dopayment.php?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}

?>
<html>
<title>shopware.sofort</title>
<head>
<script type="text/javascript" src="moomin.js"></script>
<link href="<?php echo $URL;?>/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
<style>
body {
font-size:12px;
font-family:Arial,Geneva,Arial,Helvetica,sans-serif;
}
</style>
<?php

?>
<fieldset style="padding:10px;">
<form method="POST" action="dopayment.php" target="_parent">
<input type="hidden" name="sCoreId" value="<?php echo htmlentities($_REQUEST["sCoreId"]) ?>">
<?php if(!empty($_REQUEST["sAGBError"])) {?>
<?php echo $payment->sSYSTEM->sCONFIG['sSnippets']['sSofortAcceptOurTerms'];?>
<br />
<?php }?>
<?php if (!$payment->config["sIGNOREAGB"]){ ?>
<input type="checkbox" value="1" name="sAGB"><?php echo $payment->config["sSnippets"]["sAGBTextPaymentform"] ?><br /><br />
<?php } ?>
<input type="submit" class="btn_high_r button width_reset float_reset" style="width:190px;" value="<?php echo $payment->config["sSnippets"]["sSofortProceedToPayment"] ?>">
</form>
</fieldset>
</body>
</html>