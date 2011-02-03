<?php
require_once('clickpay.class.php');

$sClickPay = new sClickPay();
$sClickPay->sInitModule();

if(!isset($sFormFile)) $sFormFile = basename(__FILE__);
$sBasketURL = $sClickPay->sGetShopURL().'/'.$sClickPay->sGetConfig('sBASEFILE').'/sViewport,basket/sCoreId,'.$sClickPay->sGetSessionID();
$sPaymentURL = $sClickPay->sGetShopURL().'/'.$sClickPay->sGetConfig('sBASEFILE').'/sViewport,admin/sAction,payment/sTarget,sale/sUseSSL,1/sCoreId,'.$sClickPay->sGetSessionID();

$sAGBError = false;
$sGiroPayError = false;
$sErrorMessages = array();

if(empty($_REQUEST['sAction'])&&$sClickPay->sGetConfig('sIGNOREAGB')&&empty($_REQUEST['sGiroPay'])&&empty($_REQUEST['sELV']))
{
	$_REQUEST['sAction'] = 'sale';
}

if(!empty($_REQUEST['sAction'])&&$_REQUEST['sAction']=='sale')
{
	if(!$sClickPay->getBasket())
	{
		if(empty($sErrorMessages))
		{
			$sErrorMessages[] = 'Es ist ein unbekannter Fehler aufgetreten';
		}
		$_REQUEST['sAction'] = 'error';
	}
}

if(!empty($_REQUEST['sAction'])&&$_REQUEST['sAction']=='sale')
{
	if(!$sClickPay->sGetConfig('sIGNOREAGB')&&empty($_REQUEST['sAGB']))
	{
		$sAGBError = true;
		unset($_REQUEST['sAction']);
	}
	if(!empty($_REQUEST['sGiroPay'])||!empty($_REQUEST['sELV']))
	{
		if(empty($_REQUEST['sAccountNumber'])||empty($_REQUEST['sBankcode'])||empty($_REQUEST['sAccountFirstname'])||empty($_REQUEST['sAccountLastname']))
		{
			$sGiroPayError = true;
			unset($_REQUEST['sAction']);
		}
	}
}

if(!empty($_REQUEST['sAction'])&&$_REQUEST['sAction']=='sale')
{
	$sParams = array();
	$sParams['haendlerid'] = $sClickPay->sGetConfig('sCLICKPAYMERCHANTID');
	$sParams['haendlercode'] = $sClickPay->sGetConfig('sCLICKPAYMERCHANTCODE');
	$sParams['mwst'] = 19;
	$sParams['mwstsatz'] = 19;
	$sParams['text'] = $sClickPay->sGetConfig('sCLICKPAYTEXT');

	$sParams['referenz'] = $sClickPay->sUser['billingaddress']['customernumber'].'_'.$sClickPay->sUser['billingaddress']['firstname'].'_'.$sClickPay->sUser['billingaddress']['lastname'];
	$sParams['referenz'] = str_replace(array('Ä', 'Ü', 'Ö', 'ä', 'ü', 'ö', 'ß', ' '), array('Ae', 'Ue', 'Oe', 'ae', 'ue', 'oe', 'ss', '_'), $sParams['referenz']);
	$sParams['referenz'] = preg_replace('#[^A-Za-z0-9_]#', '', $sParams['referenz']);
	
	$sParams['bruttobetrag'] = $sClickPay->sFormatPrice($sClickPay->sGetAmount());
	$sParams['waehrung'] =  !empty($sClickPay->sSYSTEM->sCurrency['currency']) ? $sClickPay->sSYSTEM->sCurrency['currency'] : 'EUR';
	$sParams['_language'] = $sClickPay->sGetLanguage();
	$sParams['_buchen'] = (int) $sClickPay->sGetConfig('sCLICKPAYDIRECTBOOK');
	$sParams['_stylesheet'] = $sClickPay->sGetShopURL().'/'.$sClickPay->sGetConfig('sCLICKPAYSTYLESHEET');
	if($sClickPay->sGetSnippet('sClickPayButtonCancel'))
		$sParams['_ButtonTextCancel'] = $sClickPay->sGetSnippet('sClickPayButtonCancel');
	if($sClickPay->sGetSnippet('sClickPayButtonOK'))
		$sParams['_ButtonTextOK'] = $sClickPay->sGetSnippet('sClickPayButtonOK');
	
	if(!empty($_REQUEST['sGiroPay']))
	{
		$sParams['blz'] = $_REQUEST['sBankcode'];
		$sParams['kontonummer'] = $_REQUEST['sAccountNumber'];
		$sParams['kontoinhaber'] = $_REQUEST['sAccountFirstname'].' '.$_REQUEST['sAccountLastname'];
	}
	elseif(!empty($_REQUEST['sELV']))
	{
		$sParams['spr_referenz'] = $sParams['referenz']; unset($sParams['referenz']);
		$sParams['spr_haendlerid'] = $sParams['haendlerid']; unset($sParams['haendlerid']);
		$sParams['spr_haendlercode'] = $sParams['haendlercode']; unset($sParams['haendlercode']);
		$sParams['spr_betrag'] = $sParams['bruttobetrag']; unset($sParams['bruttobetrag']);
		$sParams['spr_waehrung'] = $sParams['waehrung']; unset($sParams['waehrung']);
		$sParams['spr_BLZ'] = $_REQUEST['sBankcode'];
		$sParams['spr_kontonummer'] = $_REQUEST['sAccountNumber'];
		$sParams['spr_vorname'] = $_REQUEST['sAccountFirstname'];
		$sParams['spr_nachname'] = $_REQUEST['sAccountLastname'];
		$sParams['spr_buchen'] = (int) $sClickPay->sGetConfig('sCLICKPAYELVDIRECTBOOK'); unset($sParams['_buchen']);
	}
	elseif(!empty($_REQUEST['sLuuPay']))
	{
	}
	else 
	{
		$sParams['karteninhaber'] = $sClickPay->sUser['billingaddress']['firstname'].' '.$sClickPay->sUser['billingaddress']['lastname'];
	}
	$sBaseParams = array();
	$sBaseParams['sUniqueID'] = md5(uniqid(rand()));
	$sBaseParams['sCoreID'] = $sClickPay->sGetSessionID();
	$sBaseParams['sLanguage']	= $sClickPay->sSYSTEM->sLanguage;
	$sBaseParams['sCurrency'] = $sClickPay->sSYSTEM->sCurrency['id'];
	$sBaseParams['sSubShop'] = $sClickPay->sSYSTEM->_SESSION['sSubShop']['id'];
	$sBaseParams['sDispatchID'] = $sClickPay->sSYSTEM->_SESSION['sDispatch'];
	$sBaseParams['sPaymentID'] = $sClickPay->sUser['additional']['user']['paymentID'];
	$sBaseParams['sUserID'] = $sClickPay->sUser['additional']['user']['id'];
	$sBaseParams['sComment'] = $_REQUEST['sComment'];
	$sBaseParams['sAmount'] = $sClickPay->sGetAmount();
	
	$sBaseQuery = http_build_query($sBaseParams, '', '&');
		
	$sNotifyQuery = $sBaseQuery.'&sTransactionID=<<KontaktID>>&sStatus=<<statuscode>>';
	$sParams['NotifyURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/notify.php?sAction=status&'.$sNotifyQuery;
	
	
	$sSuccessParams = array();
	$sSuccessParams['sUniqueID'] = $sBaseParams['sUniqueID'];
	$sSuccessParams['sCoreID'] = $sClickPay->sGetSessionID();
	$sSuccessParams['sUserID'] = $sClickPay->sUser['additional']['user']['id'];
	$sSuccessQuery = http_build_query($sSuccessParams, '', '&');
	$sSuccessQuery = $sSuccessQuery.'&sTransactionID=<<KontaktID>>&sStatus=<<statuscode>>';
	
	$sParams['SuccessURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/'.$sFormFile.'?sAction=success&'.$sSuccessQuery;
	$sParams['BackURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/'.$sFormFile.'?sAction=back&'.$sSuccessQuery;
	$sParams['FailURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/'.$sFormFile.'?sAction=fail&'.$sSuccessQuery;
	$sParams['ErrorURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/'.$sFormFile.'?sAction=error&'.$sSuccessQuery;
	$sParams['EndURL'] = $sClickPay->sGetShopURL().'/engine/connectors/clickpay/'.$sFormFile.'?sAction=success&'.$sSuccessQuery;
	
	if(!empty($_REQUEST['sLuuPay']))
	{
		$sRequestURL = 'https://www.eos-payment.de/PaymentGateway_LUUPAY.acgi';
	}
	elseif(!empty($_REQUEST['sGiroPay']))
	{
		$sRequestURL = 'https://www.eos-payment.de/onlineueberweisung.acgi';
	}
	elseif(!empty($_REQUEST['sELV']))
	{
		$sRequestURL = 'https://www.eos-payment.de/karteres.acgi';
	}
	else
	{
		if($sClickPay->sGetConfig('sCLICKPAYPOPUP'))
			$sRequestURL = 'https://www.eos-payment.de/PaymentGateway_CC.acgi';
		else
			$sRequestURL = 'https://www.eos-payment.de/PaymentGatewayMini_CC.acgi';
	}
	$sRespone = $sClickPay->sDoRequest($sRequestURL,$sParams);
	if(!empty($sRespone['kontaktid']))
	{
		$sql = '
			INSERT INTO `eos_reserved_orders` (`werbecode`, `transactionID`, `reference`, `added`, `changed`)
			VALUES (?, ?, ?, NOW(), NOW());
		';
		$sClickPay->sDB_CONNECTION->Execute($sql,array(
			$sRespone['werbecode'],
			$sRespone['kontaktid'],
			isset($sRespone['spr_referenz']) ? $sRespone['spr_referenz'] : $sRespone['referenz']
		));
		$sql = 'UPDATE s_order SET transactionID=? WHERE userID=? AND temporaryID=? AND status=-1';
		$sClickPay->sDB_CONNECTION->Execute($sql,array(
			$sRespone['kontaktid'],
			$sClickPay->sUser['additional']['user']['id'],
			$sClickPay->sGetSessionID()
		));
	}

	if(!empty($sRespone['URL']))
	{
		header('Location: '.$sRespone['URL']);
		exit();
	}
	elseif(!empty($sRespone['status'])&&$sRespone['status']=='ERROR')
	{
		$fields = array('BLZ','blz','kontonummer','kontoinhaber','kartennummer','karteninhaber','haendlercode','haendlerid','vorname','nachname','Statuscode');
		foreach ($fields as $field)
		{
			if(isset($sRespone[$field])||isset($sRespone['spr_'.$field]))
			{
				$sErrorMessage = $sClickPay->sGetClickPayErrorMessage(isset($sRespone[$field]) ? $sRespone[$field] : $sRespone['spr_'.$field],$field);
				if(!empty($sErrorMessage))
					$sErrorMessages[] = $sErrorMessage;
			}
		}
		if(empty($sErrorMessages))
		{
			$sErrorMessages[] = 'Es ist ein unbekannter Fehler aufgetreten';
		}
		unset($_REQUEST['sAction']);
	}
	elseif(!empty($sRespone['status'])&&$sRespone['status']=='OK')
	{
		$_REQUEST = $sBaseParams;
		$_REQUEST['sAction'] = 'success';
		$_REQUEST['sStatus'] = 1;
		$_REQUEST['sTransactionID'] = $sRespone['kontaktid'];
		$sClickPay->sSubmitOrder(0);
	}
}
if(!empty($_REQUEST['sAction'])&&!empty($_REQUEST['sStatus']))
{
	if($_REQUEST['sStatus']==5) {
		$_REQUEST['sAction'] = 'back';
	} elseif(in_array($_REQUEST['sStatus'], array(4, 5, 7, 10, 11))) {
		$_REQUEST['sAction'] = 'fail';
	} elseif(is_numeric($_REQUEST['sStatus']) && $_REQUEST['sStatus'] > 10) {
		$_REQUEST['sAction'] = 'fail';
	} elseif(in_array($_REQUEST['sStatus'], array(1, 2))) {
		$_REQUEST['sAction'] = 'success';
	}
}
if(!empty($_REQUEST['sAction'])&&$_REQUEST['sAction']=='success')
{
	for ($i=0;$i<20;$i++)
	{
		sleep(1);
		$sql = 'SELECT ordernumber FROM s_order WHERE status != -1 AND temporaryID=? AND userID=?';
		$sOrdernumber = $sClickPay->sDB_CONNECTION->Execute($sql,array($_REQUEST['sUniqueID'],$_REQUEST['sUserID']));
		if(!empty($sOrdernumber)) break;
	}
	$sSuccessURL = $sClickPay->sGetShopURL().'/'.$sClickPay->sGetConfig('sBASEFILE').'/sViewport,sale/sAction,doSale/sRefererAllowed,1/sCoreId,'.$sClickPay->sGetSessionID().'/sUniqueID,'.$_REQUEST['sUniqueID'].'/';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
	<head>
		<title>ClickPay Interface</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="Pragma" content="no-cache"> 
   		<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"> 
   		<meta http-equiv="Expires" content="0">
   		<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo dirname($sClickPay->sGetConfig('sTEMPLATEPATH'))?>/0/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
   		<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo $sClickPay->sGetConfig('sTEMPLATEPATH')?>/de/media/css/paymentframe.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo $sClickPay->sGetConfig('sCLICKPAYIFRAMESTYLESHEET')?>" rel="stylesheet" type="text/css" media="screen" />
		<!--[if lte IE 6]>
			<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo dirname($sClickPay->sGetConfig('sTEMPLATEPATH'))?>/0/de/media/css/lteie6.css" rel="stylesheet" type="text/css" />
			<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo $sClickPay->sGetConfig('sTEMPLATEPATH')?>/de/media/css/lteie6.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		<!--[if IE 7]>
			<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo dirname($sClickPay->sGetConfig('sTEMPLATEPATH'))?>/0/de/media/css/lteie7.css" rel="stylesheet" type="text/css" />
			<link href="<?php echo $sClickPay->sGetShopURL();?>/<?php echo $sClickPay->sGetConfig('sTEMPLATEPATH')?>/de/media/css/lteie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->
	</head>
	<script type="text/javascript">
	//<!--
	function submitForm()
	{		
		var isIE6 = navigator.userAgent.toLowerCase().indexOf('msie 6') != -1;
		if(!isIE6)
		{
			<?php if(!empty($_REQUEST['sGiroPay'])):?>
			var height = 810;
			var width = 830;
			<?php else:?>
			var height = 600;
			var width = 780;
			<?php endif;?>
			left=(screen.width)?(screen.width-width)/2:100;
			top=(screen.height)?(screen.height-height)/2:100;
			settings="width="+width+",height="+height+",top="+top+",left="+left+",scrollbars=no,location=yes,directories=no,status=no,menubar=no,toolbar=no,resizable=no";
	    	sale_window = window.open('about:blank', 'sale_window', settings);
			sale_window.focus();
		}
		window.setTimeout('window.document.forms[\'sale_form\'].submit();', 1);
	}
	// -->
	</script>
	<script type="text/javascript">
	//<!--
	if(opener)
	{
		opener.location.href=self.location;
		self.close();
	}
	// -->
	</script>
	<?php if($_REQUEST['sAction']=='success'):?>
	<script type="text/javascript">
	//<!--
	if(opener && opener.top)
	{
		opener.top.location.href='<?php echo $sSuccessURL;?>';
		self.close();
	}
	else if(opener)
	{
		opener.location.href='<?php echo $sSuccessURL;?>';
		self.close();
	}
	else if(top)
	{
		top.location='<?php echo $sSuccessURL;?>';
	}
	// -->	
	</script>
	<?php endif;?>
	<body>
	<div id="container">
	<div id="content">
	<?php if(empty($_REQUEST['sAction'])):?>
		<form name="sale_form" method="GET" action="<?php echo $sFormFile;?>"<?php if(($sClickPay->sGetConfig('sCLICKPAYPOPUP')||!empty($_REQUEST['sGiroPay']))&&empty($_REQUEST['sELV'])):?> target="sale_window" onsubmit="submitForm(); return false;"<?php endif;?>>
		<input type="hidden" name="sCoreId" value="<?php echo $sClickPay->sGetSessionID(); ?>">
		<input type="hidden" name="sAction" value="sale">
		<?php if($sAGBError):?>
		<div class="error"> 
			<strong><?php echo $sClickPay->sGetSnippet('sOrderprocessacceptourterms'); ?></strong>
		</div>
		<?php endif;?>
		<?php if($sGiroPayError||$sErrorMessages):?>
		<div class="error"> 
			<strong><?php echo $sClickPay->sGetSnippet('sRegistererroroccurred'); ?></strong><br />
			<?php if($sGiroPayError):?>
			<?php echo $sClickPay->sGetSnippet('sErrorBillingAdress'); ?><br />
			<?php endif;?>
			<?php if($sErrorMessages):?>
				<?php foreach ($sErrorMessages as $sErrorMessage):?>
					<?php echo $sErrorMessage; ?><br />
				<?php endforeach;?>
			<?php endif;?>
		</div>
		<?php endif;?>
		<?php if($sClickPay->sGetConfig('sCLICKPAYSHOWCOMMENT')):?>
		<div class="none">
			<fieldset style="margin: 0pt; padding: 0pt;">
			<p style="height: 100px;" class="none">
				<label for="sComment"><?php echo $sClickPay->sGetSnippet('sOrderprocesscomment');?></label>
				<textarea style="float: left;" class="normal" onfocus="" cols="30" rows="5" name="sComment"><?php echo htmlentities($_REQUEST['sComment']);?></textarea>
			</p>
			<p style="height: 20px;" class="description">
				<?php echo $sClickPay->sGetSnippet('sOrderprocessenteradditional');?>
	    	</p>
	        </fieldset>
	    </div>
	    <?php endif;?>
	    <?php if(!empty($_REQUEST['sGiroPay'])||!empty($_REQUEST['sELV'])):?>
	    <div class="none">
			<fieldset style="margin: 0pt; padding: 0pt;">
			<p class="none">
			<label for="sAccountNumber">Kontonummer*:</label>
			<input class="normal<?php if($sGiroPayError&&empty($_REQUEST['sAccountNumber'])):?> instyle_error<?php endif;?>" style="float:left;" name="sAccountNumber" id="sAccountNumber" value="<?php echo htmlentities(empty($_REQUEST['sAccountNumber']) ? '' : $_REQUEST['sAccountNumber']);?>" type="text">
			</p>
			<p class="none">
			<label for="sBankcode">Bankleitzahl*:</label>
			<input style="float:left;" class="normal<?php if($sGiroPayError&&empty($_REQUEST['sBankcode'])):?> instyle_error<?php endif;?>" name="sBankcode" id="sBankcode" value="<?php echo htmlentities(empty($_REQUEST['sBankcode']) ? '' : $_REQUEST['sBankcode']);?>" type="text">
			</p>
			<p class="none">
			<label for="sAccountHolder">Kontoinhaber Vorname*:</label>
			<input style="float:left;" class="normal<?php if($sGiroPayError&&empty($_REQUEST['sAccountFirstname'])):?> instyle_error<?php endif;?>" name="sAccountFirstname" id="sAccountFirstname" value="<?php echo htmlentities((empty($_REQUEST['sAccountFirstname'])&&!$sGiroPayError) ? $sClickPay->sUser['billingaddress']['firstname'] : $_REQUEST['sAccountFirstname']);?>" type="text">
			</p>
			<p class="none">
			<label for="sAccountHolder">Kontoinhaber Nachname*:</label>
			<input style="float:left;" class="normal<?php if($sGiroPayError&&empty($_REQUEST['sAccountLastname'])):?> instyle_error<?php endif;?>" name="sAccountLastname" id="sAccountLastname" value="<?php echo htmlentities((empty($_REQUEST['sAccountLastname'])&&!$sGiroPayError) ? $sClickPay->sUser['billingaddress']['lastname'] : $_REQUEST['sAccountLastname']);?>" type="text">
			</p>
			<p class="description">Die mit einem * markierten Felder sind Pflichtfelder.	
			</p>
			</fieldset>
	    </div>
		<?php endif;?>
		<?php if(!$sClickPay->sGetConfig('sIGNOREAGB')):?>
	    <div class="agb_accept">
			<input type="checkbox" value="1" id="sAGB" name="sAGB"<?php if(!empty($_REQUEST['sAGB'])):?> checked<?php endif;?> style="width: 15px;float: left;" />
			<label class="chklabel" for="sAGB"<?php if ($sAGBError):?> style="color:#F00;"<?php endif;?>><?php echo $sClickPay->sGetSnippet('sAGBTextPaymentform'); ?></label>
		</div>
		<?php endif;?>
		<div class="fixfloat"></div>
		<div style="padding: 0 20px;height: 30px;" class="buttons">
	    	<a target="_top"  class="btn_def_l button width_reset" style="width:130px;" href="<?php echo $sBasketURL;?>"><?php echo $sClickPay->sGetSnippet('sOrderprocesschangebasket');?></a>
	    	<input type="submit" class="btn_high_r button width_reset" value="Zahlung durchführen"/>
	    </div>
	    <div class="fixfloat"></div>
		</form>
	<?php elseif($_REQUEST['sAction']=='success'):?>
		<div class="cat_text">
			<?php echo str_replace('{$sSuccessURL}',$sSuccessURL,$sClickPay->sGetSnippet('sClickPaySuccess'));?>
		</div>
	<?php else: ?>
		<div style="padding:20px 20px 0;height: 30px;">
			<?php if(!empty($_REQUEST['sAction'])&&$_REQUEST['sAction']=='back'):?>
				<?php echo $sClickPay->sGetSnippet('sClickPayUserAborted');?>
	    	<?php else: ?>
				<?php echo $sClickPay->sGetSnippet('sClickPayErrorAborted');?>
	    	<?php endif;?>
	    	<br />
	    </div>
	    <div class="fixfloat"></div>
	    <div style="padding: 20px;" class="buttons">
			<a target="_top" style="width:140px;" class="btn_def_l button width_reset" href="<?php echo $sBasketURL;?>"><?php echo $sClickPay->sGetSnippet('sOrderprocesschangebasket');?></a>
	    	<a target="_top" style="width:170px;" class="btn_high_r button width_reset" href="<?php echo $sPaymentURL;?>"><?php echo $sClickPay->sGetSnippet('sIndexchangepayment');?></a>
	    	<div class="fixfloat"></div>
	    </div>
	<?php endif;?>
	</div>
	</div>
	</body>
</html>