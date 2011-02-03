<?php
/*
ClickandBuy-Schnittstelle
Version 1.0
(c)2008, PayIntelligent 
*/

$path = "../";
$debug = ""; // E-Mailadresse für Debug-Informationen
include("clickandbuy.class.php");

$payment = new clickandbuyPayment("","../");
$payment->initUser();
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

//2do >> payment.class metode
$http = !empty($payment->sSYSTEM->sCONFIG['sUSESSL']) ? "https://" : "http://"; 
$basepath = $payment->sSYSTEM->sCONFIG['sBASEPATH'];
$tplpath = $payment->sSYSTEM->sCONFIG['sTEMPLATEPATH'];
$URL = $http.$basepath."/".$tplpath;

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/doPayment.php?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}

?>
<html>
<title>shopware.ClickandBuy</title>
<head>
<link href="<?php echo $URL;?>/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>
	<div id="container">
	<div id="content">
	<fieldset style="padding:20px;">
<style>
body {
font-size:12px;
font-family:Arial,Geneva,Arial,Helvetica,sans-serif;
}
</style>
<?php
$info = $payment->sSYSTEM->sCONFIG['sSnippets']['sClickAndBuyChoosenPayment'];
$abg = $payment->config["sSnippets"]["sAGBTextPaymentform"];
$button = $payment->sSYSTEM->sCONFIG['sSnippets']['sClickAndBuyProceedToPayment'];
?>


<form method="POST" action="doPayment.php">
<input type="hidden" name="sCoreId" value="<?php echo htmlentities($_REQUEST["sCoreId"]) ?>">
<?php if(!empty($_GET["sAGBError"])) {?>
<?php echo $payment->sSYSTEM->sCONFIG['sSnippets']['sClickAndBuyAcceptOurTerms'];?>
<br />
<?php }?>
<?php echo $info; ?><br />
<img src="images/clickandbuy_logo2.gif" border="0" alt="ClickandBuy"><br /><br />

<?php 
if (!$payment->config["sIGNOREAGB"]){ ?>
<input type="checkbox" value="1" name="sAGB" style="width: 15px;float: left;margin:0;">
<label class="chklabel" style="width:590px;margin-left:10px;padding:0;text-align:left;"><?php echo $abg; ?></label><br /><br />
<?php } ?>
<div class="fixfloat"></div>
<input type="submit" value="<?php echo $button; ?>" class="btn_high_r button width_reset float_reset">
</form>
</fieldset>
</div>
</div>
</body>
</html>