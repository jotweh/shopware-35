<?php
/**********************************************************
PayPal-Express-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

form.php

**********************************************************/

$path = "../";
$debug = ""; // E-Mailadresse fuer Debug-Informationen
include("paypalexpress.class.php");
$payment = new paypalexpressPayment($debug,"../");

$payment->initUser();
$userData = $payment->sUser;

$sAGB = $payment->sSYSTEM->_SESSION['sAGB'];
$token = urlencode($payment->sSYSTEM->_SESSION['token']);
$PayerID = urlencode($payment->sSYSTEM->_SESSION['payer_id']);

$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];

$agb = $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressFormAcceptAGB'];
$info = $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressFormInfo'];
$button = $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressFormButton'];
$agb_error = $payment->sSYSTEM->sCONFIG['sSnippets']['sPaypalexpressFormAgbError'];


if (($choosenPaymentMean == "paypalexpress") && !empty($token) && !empty($PayerID)) {

	$action = "DoExpressCheckoutPayment.php";
	$checkAGB = true;

	if ($payment->sSYSTEM->sLanguage == 1) $button = "Jetzt kaufen";
	else $button = "Buy now";

} else {

	if ($payment->sSYSTEM->_SESSION['GuestUser'] != "1" && $payment->sSYSTEM->sMODULES['sAdmin']->sCheckUser()) {
		$action = "doPaymentSUser.php";
		$checkAGB = true;		
	} else {
		$action = "doPaymentGuest.php";
		$checkAGB = false;
	}
}

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/'.$action.'?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}


?>
<html>
<title>shopware.PayPal-Express</title>
<head>
	<link href="../../../templates/0/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
	<!--[if lte IE 6]>
		<link href="../../../templates/0/de/media/css/lteie6.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="../../../templates/0/de/media/css/lteie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->


</head>
<body>


<style>
body {
font-size:12px;
font-family:Arial,Geneva,Arial,Helvetica,sans-serif;
}
</style>


<form method="POST" name="formx" action="<?php echo $action; ?>" target="_top">
<div style="width:600px; padding:10px 10px;text-align:left;">
	<?php
		if (!empty($info)) echo $info;
	  
		if (!$payment->config["sIGNOREAGB"] && $checkAGB == true){ 
	?>
			<br /><input type="checkbox" value="1" name="sAGB" />
	<?php 
			if ($sAGB == 'error') {			
				echo $agb_error;
			} else {
				echo $agb;			
			}
		} 
	?>
</div>
<div class="buttons">
	<a href="javascript:document.formx.submit();" title="<?php echo $button; ?>"  class="btn_high_r button width_reset float_reset" style="width:250px;margin-left:10px;"><?php echo $button; ?></a>
</div>
</form>


</body>
</html>