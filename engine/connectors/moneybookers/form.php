<?php
$path = "../";
include("moneybookers.class.php");
$payment = new moneybookersPayment("/dev/null","../");
$payment->initUser();
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

#echo '<pre>'.print_r($userData, 1).'</pre>';
#echo '<pre>'.print_r($payment->config, 1).'</pre>';
#echo '<pre>'.print_r($payment, 1).'</pre>';
#echo $payment->user_id;

if(!empty($payment->config['sIGNOREAGB'])) {
	header('Location: '.dirname($_SERVER['PHP_SELF']).'/dopayment.php?sCoreId='.urlencode($_REQUEST['sCoreId']));
	exit;
}

$style = @constant('sMONEYBOOKERS_STYLE');
if (!empty($style)){
  echo '<link rel="stylesheet" type="text/css" media="all" href="'.$style.'" />';
}
?>
<html>
<title></title>
<head>
<link href="../../../templates/0/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
</head>
<body>


<form method="POST" action="dopayment.php">
<input type="hidden" name="sCoreId" value="<?php echo htmlentities($_REQUEST["sCoreId"]) ?>">

<?php if (!$payment->config["sIGNOREAGB"]){ ?>
<input type="checkbox" value="1" name="sAGB"><?php echo $payment->config['sSnippets']['sMoneybookersAGB']?><br /><br />
<?php } ?>
<input type="submit" value="<?php echo $payment->config['sSnippets']['sMoneybookersGoOn']?>">
</form>
</body>
</html>