<?php
$path = "../";
include("heidelpay.class.php");
$payment = new heidelpayPayment("/dev/null","../");
$payment->initUser();
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

#echo '<pre>'.print_r($userData, 1).'</pre>';
#echo '<pre>'.print_r($payment->config, 1).'</pre>';
#echo $payment->user_id;

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/dopayment.php?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}

$style = @constant('sHEIDELPAY_STYLE');
if (!empty($style)){
  echo '<link rel="stylesheet" type="text/css" media="all" href="'.$style.'" />';
}
?>
<html>
<title></title>
<head>
<link href="../../../templates/0/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body style="padding:25px;">
<form method="POST" action="dopayment.php">
<input type="hidden" name="sCoreId" value="<?php echo htmlentities($_REQUEST["sCoreId"]) ?>">

<?php if (!$payment->config["sIGNOREAGB"]){ ?>
<input type="checkbox" value="1" name="sAGB"><?php echo $payment->config['sSnippets']['sHeidelpayAGB']?><br /><br />
<?php } ?>
<input type="submit" value="<?php echo $payment->config['sSnippets']['sHeidelpayGoOn']?>" class="btn_high_r button width_reset float_reset">
</form>
</body>
</html>