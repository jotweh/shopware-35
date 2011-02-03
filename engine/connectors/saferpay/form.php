<?php
/**********************************************************
Saferpay-Schnittstelle
Version 1.0
(c)2009, Payintelligent 

form.php

**********************************************************/

$path = "../";
$debug = ""; // E-Mailadresse fuer Debug-Informationen
include("saferpay.class.php");
$payment = new saferpayPayment($debug,"../");


$payment->initUser();
$userData = $payment->sUser;

$sAGB = $payment->sSYSTEM->_SESSION['sAGB'];
$saferpayTestsystem = $payment->saferpayTestsystem;
$saferpayAccountID = $payment->saferpayAccountID;
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/doPayment.php?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}

?>
<html>
<title>Shopware.Saferpay</title>
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
<div style="width:600px; padding:10px 10px;text-align:left;">
<?php

if ($payment->sSYSTEM->sLanguage == 1) include("language_de.php");
else include("language_en.php");

$agb = $sLang["saferpay"]["terms"];
$info = $sLang["saferpay"]["info"];
$button = $sLang["saferpay"]["continue"];
$agb_error = "<span style=\"color:#F00;\">".$agb."</span>";		
$paymentMeanError = $sLang["saferpay"]["paymentMeanError"];; 

if (($choosenPaymentMean == "Saferpay")) {
	$action = "doPayment.php";
} else {
	echo "<br /><span style=\"color:#F00;\">".$paymentMeanError."</span>";
	exit();
}

if(((substr($saferpayAccountID, 0, 6) == "99867-") AND ($saferpayTestsystem == "0")) OR ((substr($saferpayAccountID, 0, 6) != "99867-") AND ($saferpayTestsystem == "1"))) {
	echo "<br /><span style=\"color:#F00;\">".$sLang["saferpay"]["testsystemError"]."</span>";
	exit();
}


?>

<form method="POST" name="formx" action="<?php echo $action; ?>">
	<?php
		if (!empty($info)) echo $info;
	  
		if (!$payment->config["sIGNOREAGB"]){ 
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
	<a href="javascript:document.formx.submit();" title="<?php echo $button; ?>"  class="btn_high_r button width_reset float_reset" style="margin: 10px 0 0 10px;width:270px;"><?php echo $button; ?></a>
</div>
</form>


</body>
</html>