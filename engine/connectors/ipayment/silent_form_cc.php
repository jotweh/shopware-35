<?php
/*
	iPayment-Schnittstelle
	Version 2
	(c)2009, shopware AG
*/
$path = "../";
include("ipayment.class.php");
$sPayment = new ipaymentPayment("/dev/null","../");
$sPayment->verbose = true;
$sPayment->catchErrors();
$sPayment->initUser();
$choosenPaymentMean = $sPayment->sUser["additional"]["payment"]["name"];
$userData = $sPayment->sUser;
$sSystem->_SESSION[$config["sBOOKINGSECRETKEY"]] = false;

$account_id   = $sPayment->config["sIPAYMENT_ACCOUNTID"];     // Your Ipayment Account-ID
$trxuser_id   = $sPayment->config["sIPAYMENT_USERID"];        // Your Ipayment User-ID
$trxpassword  = $sPayment->config["sIPAYMENT_TRANSACTIONPW"]; // Your Transaction password
$security_key= $sPayment->config["sIPAYMENT_SECURITYKEY"];
$trx_amount = round($sPayment->getAmount()*100,0);
$trx_currency = $sPayment->sSYSTEM->sCurrency["currency"] ? $sPayment->sSYSTEM->sCurrency["currency"] : "EUR";
$bookingId = md5(uniqid(rand()));  
$custom = session_id()."|".$bookingId."|".$sPayment->sSYSTEM->sLanguage."|".$sPayment->sSYSTEM->sCurrency["id"];
$custom .= "|".$sPayment->sSYSTEM->_SESSION["sSubShop"]["id"]."|".$sPayment->sSYSTEM->_SESSION["sDispatch"];
$trx_securityhash = md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$security_key);

if(isset($userData['billingaddress']['firstname'])&&!isset($_POST['firstname']))
	$_POST['firstname'] = $userData["billingaddress"]['firstname'];
if(isset($userData['billingaddress']['lastname'])&&!isset($_POST['lastname']))
	$_POST['lastname'] = $userData["billingaddress"]['lastname'];
if(isset($userData['billingaddress']['street'])&&!isset($_POST['street']))
	$_POST['street'] = $userData['billingaddress']['street'];
if(isset($userData['billingaddress']['streetnumber'])&&!isset($_POST['nr']))
	$_POST['nr'] = $userData["billingaddress"][streetnumber];
if(isset($userData["billingaddress"]['zipcode'])&&!isset($_POST['zipcode']))
	$_POST['zipcode'] = $userData["billingaddress"][zipcode];
if(isset($userData["billingaddress"]['city'])&&!isset($_POST['city']))
	$_POST['city'] = $userData["billingaddress"][city];
if(isset($userData['additional']['country']['countryiso'])&&!isset($_POST['countryiso']))
	$_POST['countryiso'] = $userData['additional'][country][countryiso];
if(isset($userData['additional']['user']['email'])&&!isset($_POST['email']))
	$_POST['email'] = $userData['additional']['user']['email'];
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
	<head>
		<title>iPayment Interface</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="Pragma" content="no-cache"> 
   		<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"> 
   		<meta http-equiv="Expires" content="0">
  	 	<script type="text/javascript" src="moomin.js"></script>
  	 		<link rel="stylesheet" type="text/css" href="ipayment.css" media="screen" />
  	 	<link href="../../../templates/0/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
   		<link rel="stylesheet" type="text/css" href="ipayment.css" media="screen" />
</head>
<body>
<script>
function manageChange(value){
	if (value){
		$('ccform_submit').setStyle('opacity','1');
		$('ccform_submit').removeProperty('disabled');
	}else {
		$('ccform_submit').setStyle('opacity','0.5');
		$('ccform_submit').setProperty('disabled');
	}
}
</script>
  <form method="post" action="https://ipayment.de/merchant/<?php echo $account_id; ?>/processor/2.0/">
    <input type="hidden" name="trxuser_id" value="<?php echo $trxuser_id; ?>">
    <input type="hidden" name="trxpassword" value="<?php echo $trxpassword; ?>">
	
    <input type="hidden" name="trx_paymenttyp" value="cc">
	<?php if(!empty($sPayment->config["sIPAYMENT_RESERVE"])){ ?>
    <input type="hidden" name="trx_typ" value="preauth">
	<?php }	?>
    <input type="hidden" name="silent" value="1">

    <input type="hidden" name="trx_amount" value="<?php echo $trx_amount; ?>">
    <input type="hidden" name="trx_currency" value="<?php echo $trx_currency; ?>">
    <input type="hidden" name="trx_securityhash" value="<?php echo $trx_securityhash; ?>">

    <input type="hidden" name="silent_error_url" value="<?php echo $_SERVER['SERVER_PORT'] == "80" ? 'http://' : 'https://' ?><?php echo $sPayment->config["sBASEPATH"] ?>/engine/connectors/ipayment/back_from_silent.php">
    <input type="hidden" name="hidden_trigger_url" value="<?php echo $_SERVER['SERVER_PORT'] == "80" ? 'http://' : 'https://' ?><?php echo $sPayment->config["sBASEPATH"] ?>/engine/connectors/ipayment/hidden_trigger.php">
    <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['SERVER_PORT'] == "80" ? 'http://' : 'https://' ?><?php echo $sPayment->config["sBASEPATH"] ?>/<?php echo $sPayment->config["sBASEFILE"] ?>/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,<?php echo session_id(); ?>/sUniqueID,<?php echo $bookingId ?>">
    <input type="hidden" name="noparams_on_redirect_url" value="1">

    <input type="hidden" name="client_name" value="Shopware <?php echo $sPayment->config["sVERSION"] ?>">
    <input type="hidden" name="client_version" value="Version 2">
    <input type="hidden" name="from_ip" value="<?php echo htmlspecialchars($_SERVER["REMOTE_ADDR"]);?>">
    <input type="hidden" name="shopper_id" value="<?php echo htmlspecialchars(session_id());?>">
    <input type="hidden" name="browser_user_agent" value="<?php echo htmlspecialchars($_SERVER["HTTP_USER_AGENT"]);?>">
    <input type="hidden" name="browser_accept_headers" value="<?php echo htmlspecialchars($_SERVER["HTTP_ACCEPT"]);?>">

	<input type="hidden" name="custom" value="<?php echo $custom; ?>">
	<script language="javascript">
	document.writeln("<input name=\"cc_number\" id=\"cc_number\" type=\"hidden\" value=\"\">");
	</script>
    <table cellpadding="0" cellspacing="0" width="100%" border="0">
      <tr>
        <td>
	      <?php if(!empty($sPayment->config["sIPAYMENT_3DSECURE"])){ ?>
		   <img src="ipayment_alle.jpg" width="130" height="200" alt="3D-Secure" style="float: right; margin-right: 50px;" />
		  <?php } ?>
          <table border="0">
            <tr>
              <td valign="top" class="td Stil3">
                 <?php echo $sPayment->config['sSnippets']['sIPaymentCreditcardHolder']; ?>
              </td>
              <td colspan="2" class="td">
                <input name="addr_name" type="text" value="<?php echo htmlspecialchars($_POST['firstname']." ".$_POST["lastname"]); ?>" size="34" maxlength="50">              
				<input name="addr_street" type="hidden" value="<?php echo htmlspecialchars($_POST['street']." ".$_POST["nr"]); ?>" size="34" maxlength="50">
				<input name="addr_zip" type="hidden" value="<?php echo htmlspecialchars($_POST['zipcode']); ?>" size="6" maxlength="10">
				<input name="addr_city" type="hidden" value="<?php echo htmlspecialchars($_POST["city"]); ?>" size="26" maxlength="50">
				<input name="addr_country" type="hidden" value="<?php echo htmlspecialchars($_POST["countryiso"]); ?>" size="3" maxlength="3">
				<input name="addr_email" type="hidden" value="<?php echo htmlspecialchars($_POST["email"]); ?>" size="34" maxlength="50">
				</td>
            </tr>
			
            <tr>
              <td height="20" colspan="3" class="td Stil3"></td>
            </tr>

            <tr>
              <td valign="top" class="td Stil3">
              	<?php echo $sPayment->config['sSnippets']['sIPaymentAmount']; ?>
              </td>
              <td colspan="2" class="td">
               
                  <span class="Stil3"><b>
                    <?php echo number_format($trx_amount / 100, 2, ",", ".")." ".$trx_currency;?>
                  </b> </span></td>
            </tr>

            <tr>
              <td height="20" colspan="3" class="td Stil3"></td>
            </tr>

            <tr>
              <td valign="top" class="td Stil3">
	               <?php echo $sPayment->config['sSnippets']['sIPaymentCreditcardNumber']; ?>
	          </td>
              <td colspan="2" class="td">
              	<noscript>
              	<input name="cc_number" type="text" value="" size="34" maxlength="20">
              	</noscript>
              	<script language="javascript">
              	function checkKK(field,next){
	              	field.value = field.value.replace(/\D/, "");
					if (field.value.length > 4){
					    field.value = field.value.substr(0,4);
					}
					if (field.value.length == 4 && next){
					    document.getElementById(next).focus();
					}
					// Refresh hidden field
					document.getElementById('cc_number').value = document.getElementById('cc_number1').value + document.getElementById('cc_number2').value + document.getElementById('cc_number3').value + document.getElementById('cc_number4').value;
                 }
              	
                document.writeln("<input autocomplete=Off onkeyup=\"checkKK(this,'cc_number2');\" name=\"cc_number1\" id=\"cc_number1\" maxlength=\"4\" style=\"width:50px\">");
              	document.writeln("<input autocomplete=Off onkeyup=\"checkKK(this,'cc_number3');\" name=\"cc_number2\" id=\"cc_number2\" maxlength=\"4\" style=\"width:50px\">");
              	document.writeln("<input autocomplete=Off onkeyup=\"checkKK(this,'cc_number4');\" name=\"cc_number3\" id=\"cc_number3\" maxlength=\"4\" style=\"width:50px\">");
              	document.writeln("<input autocomplete=Off onkeyup=\"checkKK(this,'');\" name=\"cc_number4\" id=\"cc_number4\" maxlength=\"4\" style=\"width:50px\">");
              	</script>
              </td>
            </tr>

            <tr>
              <td valign="top" class="td Stil3">
              	<?php echo $sPayment->config['sSnippets']['sIPaymentCreditCheckDigit']; ?>
              </td>
              <td colspan="4" class="td Stil3">
                <input type="text" name="cc_checkcode" size="4" maxlength="4" value=""><br /><br />
                <?php echo $sPayment->config['sSnippets']['sIPaymentInfoField']; ?>
              </td>
            </tr>

            <tr>
              <td valign="top" class="td Stil3">
                <?php echo $sPayment->config['sSnippets']['sIPaymentCreditValidUntil']; ?>
              </td>
              <td colspan="2" class="td Stil3">
                <select name="cc_expdate_month" style="width:55px">
                  <option>01</option>
                  <option>02</option>
                  <option>03</option>
                  <option>04</option>
                  <option>05</option>
                  <option>06</option>
                  <option>07</option>
                  <option>08</option>
                  <option>09</option>
                  <option>10</option>
                  <option>11</option>
                  <option>12</option>
                </select>
                &nbsp;/&nbsp;
                <select name="cc_expdate_year" style="width: 155px">
                <?php
					for ($i=date("Y");$i<=date("Y")+10;$i++){
						echo "<option>$i</option>";
					}
                ?>
                </select>              </td>
            </tr>
             <tr>
	      <td class="td Stil3"><?php echo $sPayment->config['sSnippets']['sIPaymentComment']; ?></td>
	      <td class="td Stil3"><br />
	      	<textarea name="sComment"><?php echo htmlentities(Shopware()->Session()->sComment);?></textarea>
	      </td>
	      </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <table>
           <tr>
           <td height=20><br /><br /></td>
           </tr>
          <tr>
          <td colspan="2" style="font-size:11px;">
          	<?php if (empty($sPayment->config["sIGNOREAGB"])){ ?>
			 <input type="checkbox" value="1" name="sAGB" onchange="manageChange(this.checked);" onclick="manageChange(this.checked);" style="margin-right:10px;cursor:pointer;">
			 <?php echo $sPayment->config["sSnippets"]["sAGBTextPaymentform"]; ?><br /><br />
			<?php } ?>
          </td>
          </tr>
            <tr>
              <td>
			  	<?php echo $sPayment->config['sSnippets']['sIPaymentSubmitButton']; ?>
			  	<?php if (empty($sPayment->config["sIGNOREAGB"])){ ?>
			  	<script language="javascript">
			  	manageChange(0);
			  	</script>
			  	<?php } ?>
              </td>
              <td style="padding-left:10px;">
                <span class="Stil3">
                	<?php echo $sPayment->config['sSnippets']['sIPaymentProcessInfo']; ?>
                </span>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </form>
</body>
</html>