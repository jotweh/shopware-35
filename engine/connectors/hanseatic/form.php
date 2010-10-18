<?php
$path = "../";
include("hanseatic.class.php");
$payment = new hanseaticPayment("/dev/null","../");
$payment->initUser();
$choosenPaymentMean = $payment->sUser["additional"]["payment"]["name"];
$userData = $payment->sUser;

#echo $payment->user_id;

$amount = $payment->getAmount();
$lowestRate = $payment->getLowestRate($amount);
$rateMonth = key($lowestRate);
$rateAmount = $lowestRate[$rateMonth];
$rateAmount = 1;
$amount*= 100;
#echo '<pre>'.print_r($payment, 1).'</pre>';
echo '<link rel="stylesheet" type="text/css" media="all" href="../../../templates/0/de/media/css/paymentframe.css" />';
$style = @constant('sHANSEATIC_STYLE');
if (!empty($style)){
  echo '<link rel="stylesheet" type="text/css" media="all" href="'.$style.'" />';
}
if (empty($rateAmount)){?>
  <?php echo$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticNoFinance']?>
<?php } else if ($amount < sHANSEATIC_MIN_CREDIT_VALUE){?>
  <?php echo$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticNoFinanceMin']?>
<?php } else if ($amount > sHANSEATIC_MAX_CREDIT_VALUE){?>
  <?php echo$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticNoFinanceMax']?>
<?php } else {?>
<form method="POST" action="dopayment.php">
<input type="hidden" name="sCoreId" value="<?php echo $_REQUEST["sCoreId"] ?>">

<div style="padding:20px;">
<?php if (!$payment->config["sIGNOREAGB"]){ ?>
<input type="checkbox" value="1" name="sAGB"><?php echo$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticAGB']?><br /><br />
<?php } ?>
<input type="submit" value="<?php echo$payment->sMODULES['sArticles']->sSYSTEM->sCONFIG['sSnippets']['sHanseaticGoOn']?>" class="btn_high_r button width_reset float_reset">
</div>

</form>
<?php }?>