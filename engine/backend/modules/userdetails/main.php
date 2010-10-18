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
// *****************
foreach ($_POST as $pKey => $pValue){
	if (!is_array($_POST[$pKey])){
		$_POST[$pKey] = htmlspecialchars(mysql_real_escape_string($pValue));
	}
}


?>
<?php
if (!$_GET["id"]) die ($sLang["userdetails"]["main_no_user"]);


if ($_GET["delete"]){
	$deleteMain = mysql_query("
	DELETE FROM s_user WHERE id={$_GET["id"]}
	");
	$deleteBilling = mysql_query("DELETE FROM s_user_billingaddress WHERE userID={$_GET["id"]}");
	$deleteShipping = mysql_query("DELETE FROM s_user_shippingaddress WHERE userID={$_GET["id"]}");
	$deleteCredit = mysql_query("DELETE FROM s_user_creditcard WHERE userID={$_GET["id"]}");
	$deleteDebit = mysql_query("DELETE FROM s_user_debit WHERE userID={$_GET["id"]}");
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />

<title>sa
</title>

</head>

<body>

<script>
parent.parent.Growl ('<?php echo htmlentities($sLang["userdetails"]["main_user_deleted"],ENT_QUOTES);?>'); 
parent.parent.sWindows.focus.close();
</script>
</body>
</html>
<?php
	
	die();	
}


if ($_POST["savePayment"]){
	$currentPayment = mysql_query("SELECT paymentID FROM s_user WHERE id = {$_GET["id"]}");
	$currentPayment = mysql_result($currentPayment,0,"paymentID");
	
	if ($currentPayment != $_POST["paymentType"]){
		$updateUser = mysql_query("UPDATE s_user SET paymentID={$_POST["paymentType"]},paymentpreset={$_POST["paymentType"]} WHERE id={$_GET["id"]}");
	}else {
		$ignorePaymentChange = true;
	}
	$sSuccess = $sLang["userdetails"]["main_change_payment"];
	
	if (count($_POST["_PAYMENT"]) && $ignorePaymentChange == false){
		
		foreach ($_POST["_PAYMENT"] as $key => $data){
			if ($key && count($data)){
				
				// Select table
				$sql = "
				SELECT `table` as tabelle FROM s_core_paymentmeans WHERE id=$key
				";
				//echo $sql;
				$getTable = mysql_query($sql);
				if (@mysql_num_rows($getTable)){
					
					$table = mysql_result($getTable,0,"tabelle");
					
					// Check if entry exists
					$sql ="
					SELECT id FROM $table WHERE userID = {$_GET["id"]}
					";
				//	echo $sql;
					$checkEntry = mysql_query($sql);
					if (@mysql_num_rows($checkEntry)){
					
						foreach ($data as $dataKey => $dataValue){
							$updateSQL[] = " $dataKey='".mysql_real_escape_string($dataValue)."'";
						}
						$updateSQL = implode(",",$updateSQL);
						
						// Update
						$sql = "
						UPDATE $table 
						SET
							$updateSQL
						WHERE userID={$_GET["id"]}
						";
						//echo $sql;
						$updateSQL = mysql_query($sql);
					}else {
						// insert
						$values[] = "userID";
						$data2[] = $_GET["id"];
						foreach ($data as $dataKey => $dataValue){
							$values[] = $dataKey;
							$data2[] = "'".mysql_real_escape_string($dataValue)."'";
						}
					
						
						// Update
						$values = implode(",",$values);
						$data = implode(",",$data2);
						$sql = "
						INSERT INTO $table 
						($values)
						VALUES($data)
						";
						//echo $sql;
						$updateSQL = mysql_query($sql);
					}
				}
			}
		}
	}
}


if ($_POST["saveMain"]){
	if ($_POST["sNewEmail"]){
		// Simply update
		$_POST["sNewEmail"] = strtolower($_POST["sNewEmail"]);
		if ($_POST["sNewEmail"]!=$userMain["email"]){
			if (ereg("^.+@.+\\..+$", $_POST["sNewEmail"])){
				$sql = "
				SELECT id FROM s_user WHERE id!={$_GET["id"]} AND email='{$_POST["sNewEmail"]}' 
				";
				
				$checkUser = mysql_query($sql);
				if (!@mysql_num_rows($checkUser)){
					$updateUser = mysql_query("UPDATE s_user SET email='{$_POST["sNewEmail"]}' WHERE id={$_GET["id"]}");
					$sSuccess = $sLang["userdetails"]["main_emailadress_changed"];
				}else {
					$sError = $sLang["userdetails"]["main_emailadress_unavailable"];
					
				}
			}else {
				$sError = $sLang["userdetails"]["main_enter_valid_emailadress"];
			}
		}
	}
	
	if ($_POST["sCustomerGroup"]!=$userMain["customergroup"]){
		$sql = "
		UPDATE s_user SET customergroup='{$_POST["sCustomerGroup"]}'
		WHERE id={$_GET["id"]} 
		";
		
		$updateCustomer = mysql_query($sql);
		if (($updateCustomer)){
			$sSuccess = $sLang["userdetails"]["main_customergroup_changed"];
		}else {
			$sError = $sLang["userdetails"]["main_cant_change_customergroup"];
			
		}
	}
	
	if ($_POST["sNewPassword"] && $_POST["sNewPasswordConfirmation"]){
		if ($_POST["sNewPassword"]==$_POST["sNewPasswordConfirmation"]){
			if (strlen($_POST["sNewPassword"])>=$sCore->sCONFIG["sMINPASSWORD"]){
				// Update
				$password = md5($_POST["sNewPassword"]);
				$updateUser = mysql_query("UPDATE s_user SET password='$password' WHERE id={$_GET["id"]}");
				$sSuccess = "Password wurde geändert";
			}else {
				$sError = "Das neue Passwort muss aus mindestens ".$sCore->sCONFIG["sMINPASSWORD"]." Zeichen bestehen";
			}
		}else {
			$sError = $sLang["userdetails"]["main_type_your_password_two_times"];
		}
	}
	if (!empty($_POST["sCustomernumber"])){
		$update = mysql_query("
		UPDATE s_user_billingaddress SET customernumber = '{$_POST["sCustomernumber"]}' WHERE userID = {$_GET["id"]}
		");
	}
}

if ($_POST["internalcomment"]){
	$comment = html_entity_decode($_POST["internalcomment"]);
	
	$sql = "
	UPDATE s_user SET internalcomment='$comment'
	WHERE id={$_GET["id"]} 
	";
	$updateCustomer = mysql_query($sql);
}

if ($_GET["deactivate"]){
	$updateUser = mysql_query("UPDATE s_user SET active=0 WHERE id={$_GET["id"]}");
	$sSuccess = $sLang["userdetails"]["main_account_lock"];
}
if ($_GET["addaccount"]){
	$updateUser = mysql_query("UPDATE s_user SET accountmode=0 WHERE id={$_GET["id"]}");
	$sSuccess = "Das Kundenkonto des Kunden wurde erfolgreich aktiviert.";
}
if ($_GET["activate"]){
	$updateUser = mysql_query("UPDATE s_user SET active=1 WHERE id={$_GET["id"]}");
	$sSuccess = $sLang["userdetails"]["main_account_unlock"];
}
$queryUserMain = mysql_query("
SELECT accountmode,email,internalcomment,active,DATE_FORMAT(firstlogin,'%d.%m.%Y') AS firstlogin,paymentID, DATE_FORMAT(lastlogin,'%d.%m.%Y %H:%i') AS lastlogin, customergroup,subshopID,s_user.language AS language,subshopID, s.name AS subshopName,domainaliase
FROM s_user 
LEFT JOIN s_core_multilanguage AS s ON subshopID = s.id 
WHERE s_user.id={$_GET["id"]}
");


if (!@mysql_num_rows($queryUserMain)) die ($sLang["userdetails"]["main_could_not_fetch"]);
$userMain = mysql_fetch_array($queryUserMain);

if (empty($userMain["domainaliase"])){
	$domain = "http://".$sCore->sCONFIG["sBASEPATH"]."/".$sCore->sCONFIG["sBASEFILE"];
}else {
	$userMain["domainaliase"] = explode("\n",$userMain["domainaliase"]);
	$temp = str_replace($sCore->sCONFIG["sHOST"],$userMain["domainaliase"][0],$sCore->sCONFIG["sBASEPATH"]);
	$domain = "http://".$temp."/".$sCore->sCONFIG["sBASEFILE"];
	
}
$domain = str_replace("\n","",$domain);
$domain = str_replace("\r","",$domain);

$domainDirect = str_replace($sCore->sCONFIG["sBASEFILE"],"",$domain);



// Query all customer-groups
$getCustomerGroups = mysql_query("
SELECT groupkey, description FROM s_core_customergroups ORDER BY id ASC
");


if ($_POST["saveBilling"]){
	
	$birthday_str = sprintf("%s-%s-%s", $_POST['birthyear'], $_POST['birthmonth'], $_POST['birthday']);
	
	$sql = "
	UPDATE s_user_billingaddress SET 
	company='{$_POST["billing_company"]}',
	salutation='{$_POST["billing_salutation"]}',
	department='{$_POST["billing_department"]}',
	firstname='{$_POST["billing_firstname"]}',
	lastname='{$_POST["billing_lastname"]}',
	street='{$_POST["billing_street"]}',
	streetnumber='{$_POST["billing_streetnumber"]}',
	zipcode='{$_POST["billing_zipcode"]}',
	city='{$_POST["billing_city"]}',
	phone='{$_POST["billing_phone"]}',
	fax='{$_POST["billing_fax"]}',
	countryID='{$_POST["billing_country"]}',
	ustid='{$_POST["billing_ustid"]}',
	text1='{$_POST["billing_text1"]}',
	text2='{$_POST["billing_text2"]}',
	text3='{$_POST["billing_text3"]}',
	text4='{$_POST["billing_text4"]}',
	text5='{$_POST["billing_text5"]}',
	text6='{$_POST["billing_text6"]}',
	birthday='{$birthday_str}'
	WHERE userID={$_GET["id"]}
	";
	
	$updateUser = mysql_query($sql);
	
	if ($updateUser){
		$sSuccess = $sLang["userdetails"]["main_changes_saved"];
	}else {
		$sError = $sLang["userdetails"]["main_changes_not_saved"];
	}
	

}if ($_POST["saveShipping"]){

	$checkShipping = mysql_query("
	SELECT id FROM s_user_shippingaddress WHERE userID={$_GET["id"]}
	");
	
	if (@mysql_num_rows($checkShipping)){
		// Already there, shipping-adress just needed to get updated
		
		$sql = "
		UPDATE s_user_shippingaddress SET 
		company='{$_POST["company"]}',
		salutation='{$_POST["salutation"]}',
		department='{$_POST["department"]}',
		firstname='{$_POST["firstname"]}',
		lastname='{$_POST["lastname"]}',
		street='{$_POST["street"]}',
		streetnumber='{$_POST["streetnumber"]}',
		zipcode='{$_POST["zipcode"]}',
		city='{$_POST["city"]}',
		countryID='{$_POST["country"]}',
		text1='{$_POST["text1"]}',
		text2='{$_POST["text2"]}',
		text3='{$_POST["text3"]}',
		text4='{$_POST["text4"]}',
		text5='{$_POST["text5"]}',
		text6='{$_POST["text6"]}'
		WHERE userID={$_GET["id"]}
		";

	}else {
		// New shipping-adress,just insert
		$sql = "
		INSERT INTO s_user_shippingaddress
		(userID,company,department,salutation, firstname, lastname, street, streetnumber,
		zipcode, city,countryID)
		VALUES (
		{$_GET["id"]},
		'{$_POST["company"]}',
		'{$_POST["department"]}',
		'{$_POST["salutation"]}',
		'{$_POST["firstname"]}',
		'{$_POST["lastname"]}',
		'{$_POST["street"]}',
		'{$_POST["streetnumber"]}',
		'{$_POST["zipcode"]}',
		'{$_POST["city"]}',
		'{$_POST["country"]}'
		)
		";
	}
	
	$updateUser = mysql_query($sql);
	
	
	if ($updateUser){
		$sSuccess = $sLang["userdetails"]["main_changes_saved"];
	}else {
		$sError = $sLang["userdetails"]["main_changes_not_saved"];
	}
	
}





$queryUserBillingAddress = mysql_query("
SELECT * FROM s_user_billingaddress WHERE userID={$_GET["id"]}
");

if (!mysql_num_rows($queryUserBillingAddress)) die($sLang["userdetails"]["main_could_not_fetch_billing-adress"]);

$userBilling = mysql_fetch_array($queryUserBillingAddress);

$queryUserShippingAddress = mysql_query("
SELECT * FROM s_user_shippingaddress WHERE userID={$_GET["id"]}
");

if (!@mysql_num_rows($queryUserShippingAddress)){
	$userShipping = $userBilling;
}else {
	$userShipping = mysql_fetch_array($queryUserShippingAddress);
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>
<style>
label {
	width:100px;
}
</style>
<body>
<script type="text/javascript">
function deleteClient(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["userdetails"]["main_should_the_customer"]." ".htmlentities($userBilling["firstname"],ENT_QUOTES)." ".htmlentities($userBilling["lastname"],ENT_QUOTES)." ".$sLang["userdetails"]["main_really_be_deleted"] ?>',window,'deleteClient',ev);

}
	
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "save":
		if (precheckMain()){
			$('mainsave').submit();
		}
		break;
		case "deleteClient":
			window.location.href = "<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]."&delete=".$_GET["id"] ?>";
		break;
	}
}


<?php
if ($sError){
?>
parent.parent.Growl('<?php echo $sError; ?>');
parent.parent.sWindows.focus.shake(15);
<?php
}
?>

<?php
if ($sSuccess){
?>
parent.parent.Growl('<?php echo $sSuccess; ?>');
<?php
}
?>


		
</script>


<script>
function precheckMain(){
	var errors;
	if (!$('email').getValue()){
		parent.parent.Growl('<?php echo $sLang["userdetails"]["main_please_enter_email"] ?>');
		parent.sWindows.focus.shake(15);
		return false;
	}
	if ($('pass1').getValue()!=$('pass2').getValue()){
		parent.parent.Growl('<?php echo $sLang["userdetails"]["main_type_your_password_two_times"] ?>');
		parent.parent.sWindows.focus.shake(15);
		return false;
	}
	return true;
}
</script>
	<!-- Maindata -->
	
<form id="mainsave" name="save" method="post" action="<?php echo $PHP_SELF."?id=".$_GET["id"]."&ext=".$_GET["ext"]?>" onSubmit="return precheckMain()">
	<div style="height:10px;"></div>
<div style="float:left; width:49%; padding-left:5px; min-width:390px;">
	<fieldset>
		<legend><?php echo $sLang["userdetails"]["main_Properties"] ?></legend>
	
		<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>" />
	  	<input type="hidden" name="saveMain" value="1" />
	
	
		<ul>
		<li><label for="name" style="width:110px"><?php echo $sLang["userdetails"]["main_emailadress"] ?></label><input name="sNewEmail" type="text" id="email" class="w200" value="<?php echo $userMain["email"] ?>" /></li><li class="clear"></li>
		</ul>
		<ul>
		<li><label for="name" style="width:110px"><?php echo $sLang["userdetails"]["main_change_password"] ?></label><input name="sNewPassword" type="text" id="pass1" class="w200" value="" /></li><li class="clear"></li>
		</ul>
		<ul>
		<li><label for="name" style="width:110px"><?php echo $sLang["userdetails"]["main_change_password_confirm"] ?></label><input name="sNewPasswordConfirmation" type="text" id="pass2" class="w200" value="" /></li><li class="clear"></li>
		</ul>
		
		<ul>
		<li><label for="name" style="width:110px"><?php echo $sLang["userdetails"]["main_customergroup"] ?></label>
		<select name="sCustomerGroup">
		<?php
		while ($customerGroup=mysql_fetch_array($getCustomerGroups)){
		?>
			<option <?php echo $customerGroup["groupkey"]==$userMain["customergroup"] ? "selected" : "" ?> value="<?php echo $customerGroup["groupkey"]?>"><?php echo $customerGroup["description"] ?></option>
		<?php
		}
		?>
		</select>
		</li><li class="clear"></li>
		</ul>
		<ul>
		<li><label for="name" style="width:110px"><?php echo $sLang["userdetails"]["main_customernummber"] ?></label>
		<input name="sCustomernumber" type="text" id="pass2" class="w200" value="<?php echo $userBilling["customernumber"] ?>" />
		</li><li class="clear"></li>
		</ul>
		<?php
		if ($userMain["accountmode"]){
		?>
		<ul>
		<li>
		<p style="font-weight:bold;color:#F00"><?php echo $sLang["userdetails"]["main_customeraccount"] ?></p>
		</li><li class="clear"></li>
		</ul>
		<?php
		}
		?>
		<div class="clear"></div>
		<div style="float: left">
		
			<div class="clear" style="height15px;"></div>
			<div class="buttons" id="buttons">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a class="bt_icon delete" onClick="deleteClient(<?php echo $_GET["id"]?>,'')"  value="send" style="width:110px; text-decoration:none;"><?php echo $sLang["userdetails"]["main_delet_account"] ?></a></li>	
			</ul>
		</div>
		<div class="clear" style="height15px;"></div>
				<div class="buttons" id="buttons" style="margin-top:10px;">
		<ul>	
		<?php if ($userMain["active"]) { ?>
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?id=".$_GET["id"]."&deactivate=1&ext=".$_GET["ext"] ?>"  class="bt_icon forbidden" value="send" style="width:110px; text-decoration:none;"><?php echo $sLang["userdetails"]["main_disable_account"] ?></a></li>
		<?php } else { ?> 
			<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?id=".$_GET["id"]."&activate=1&ext=".$_GET["ext"]?>"  class="bt_icon accept" value="send" style="width:110px; text-decoration:none;"><?php echo $sLang["userdetails"]["main_enable_account"] ?></a></li>
		<?php } ?>
		
		<?php if ($userMain["accountmode"]) { ?>
		<div class="clear" style="height15px;"></div>
		<div class="buttons" id="buttons" style="margin-top:10px;">
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?id=".$_GET["id"]."&addaccount=1&ext=".$_GET["ext"] ?>"  class="bt_icon add" value="send" style="width:110px; text-decoration:none;">Kundenkonto erstellen</a></li>
		<?php }?> 
				
		</ul>
		</div>
		
		</div>
	</fieldset>
</div>
<div style="float:left; padding-left:5px;">
	<fieldset style="height:263px;">
		<legend>Weitere Informationen:</legend><strong>
		<?php
			$queryTotalAmount = mysql_query("
			SELECT SUM(invoice_amount) AS amount FROM s_order WHERE userID={$_GET["id"]} AND status != -1 AND status != 4
			");
		
			if (@mysql_num_rows($queryTotalAmount)){
				$totalAmount = number_format(mysql_result($queryTotalAmount,0,"amount"),2,",","");
			}else {
				$totalAmount = "0,00";
			}
			
			$queryTotalAmountInkasso = mysql_query("
			SELECT SUM(invoice_amount) AS amount FROM s_order WHERE userID={$_GET["id"]} AND cleared = 16
			");
		
			if (@mysql_num_rows($queryTotalAmountInkasso)){
				$totalAmountInkasso = number_format(mysql_result($queryTotalAmountInkasso,0,"amount"),2,",","");
			}else {
				$totalAmountInkasso = "0,00";
			}
			
			$queryOrders = mysql_query("
			SELECT COUNT(id) AS amount FROM s_order WHERE userID={$_GET["id"]} AND status != -1 AND status != 4
			");
		
			if (@mysql_num_rows($queryOrders)){
				$orders = mysql_result($queryOrders,0,"amount");
			}else {
				$queryOrders = "0";
			}
		?>
			<?php echo $sLang["userdetails"]["main_registrated_since"] ?> <?php echo $userMain["firstlogin"]?> <br />
			<?php echo $sLang["userdetails"]["main_last_login"] ?> <?php echo $userMain["lastlogin"] ?><br />
			<?php if ($userMain["language"]) { ?>Sprache: <?php echo $userMain["language"] ? $userMain["language"] : "de" ?> <br /><?php }?>
			<?php if ($userMain["subshopID"]) { ?>Über Shop: <?php echo $userMain["subshopName"] ?> <br /><?php }?>
			<?php echo $sLang["userdetails"]["main_orders_since_registration"] ?> <?php echo $orders ?><br />
			<?php echo $sLang["userdetails"]["main_Turnover_since_registration"] ?> <?php echo $totalAmount ?> &euro;<br />
			<span style="color:#F00"><?php echo $sLang["userdetails"]["main_Paymentfailures_since_registration"] ?> <?php echo $totalAmountInkasso ?> &euro;</span>
		</strong>
		<!-- Bestellung durchführen -->
		<div style="clear:both;"></div><br />
		

			<div class="buttons" id="buttons" style="margin-left:0px;">
				<ul>
					<li id="buttonTemplate" class="buttonTemplate">
					<button onClick="doOrder();return false;" type="submit" value="send" class="button"><div class="buttonLabel">Bestellung durchführen</div></button>
					</li>	
				</ul>
			</div>
	</fieldset>
</div>
	<!-- Maindata -->
<script>
function doOrder(){
	<?php
		
	?>
	new Request({url: '../../../../backend/UserLogin?id=<?php echo $_GET["id"] ?>', 
	onFailure: function (el){
		parent.parent.Growl("Benutzer <?php echo $_GET["id"] ?> konnte nicht initialisiert werden}");
	},
	onComplete: function (response){
		if (response!="FAIL"){
			myWindow = window.open("<?php echo $domain ?>?sCoreId="+response,"Login");
			try {
			myWindow.focus();
			} catch (e){
				parent.parent.Growl('Bitte erlauben Sie das Öffnen von Popups!');	
			}
		}else {
			parent.parent.Growl("Login nicht möglich");
		}
	}
	}).get();	
		
}
</script>

<?php if($_REQUEST['ext'] == 1) { ?>
<div style="clear:both;"></div>


	<div class="buttons" id="buttons" style="margin-left:10px;">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('mainsave').submit();" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button></li>	
		
			
		
		</ul>
		</div>

<?php } ?>

<div style="clear:both;"></div>
	
<fieldset style="margin-left:10px; margin-right:10px;margin-top:-40px; position:relative; clear:both;float:none;">
<legend>Kommentare / Kommunikation! Wird nicht ausgegeben!</legend>
<ul>
<li><textarea name="internalcomment" class="w200" rows="8" style="width:500px"><?php echo $userMain["internalcomment"]?></textarea></li><li class="clear"/>
</ul>
</fieldset>
<div style="clear:both;"></div>
	
	
<!--<form id="billingsave" name="save" method="post" action="<?php echo $PHP_SELF."?id=".$_GET["id"] ?>" onSubmit="return precheckMain()">-->
<fieldset style="margin-left:10px; margin-right:10px; position:relative; clear:both;float:none;">
	<legend><?php echo $sLang["userdetails"]["main_key_data"] ?></legend>

	<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>" />
  	<input type="hidden" name="saveBilling" value="1" />
	
	
	<ul>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_Title"] ?></label>
	<select name="billing_salutation">
	<option value="mr" <?php echo $userBilling["salutation"]=="mr" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_mr"] ?></option>
	<option value="ms" <?php echo $userBilling["salutation"]=="ms" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_ms"] ?></option>
	<option value="company" <?php echo $userBilling["salutation"]=="company" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_company"] ?></option>
	</select>
	</li><li class="clear"></li>
	
	<li><label for="name"><?php echo Firma ?></label><input name="billing_company" type="text" id="txtName" class="w200" value="<?php echo $userBilling["company"] ?>" /></li><li class="clear"></li>
<li><label for="name"><?php echo $sLang["userdetails"]["main_Department"] ?></label><input name="billing_department" type="text" id="txtName" class="w200" value="<?php echo $userBilling["department"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_firstname"] ?></label><input name="billing_firstname" type="text" id="txtName" class="w200" value="<?php echo $userBilling["firstname"] ?>" /></li><li class="clear"></li>
	
	<li><label for="name"><?php echo $sLang["userdetails"]["main_lastname"] ?></label><input name="billing_lastname" type="text" id="txtName" class="w200" value="<?php echo $userBilling["lastname"] ?>" /></li><li class="clear"></li>
<li><label for="name"><?php echo $sLang["userdetails"]["main_street"] ?></label><input name="billing_street" type="text" id="txtName" class="w200" value="<?php echo $userBilling["street"] ?>" /></li><li class="clear"></li>
<li><label for="name"><?php echo $sLang["userdetails"]["main_house"] ?></label><input name="billing_streetnumber" type="text" id="txtName" class="w200" value="<?php echo $userBilling["streetnumber"] ?>" /></li><li class="clear"></li>
<li><label for="name"><?php echo $sLang["userdetails"]["main_Postal_Code"] ?></label><input name="billing_zipcode" type="text" id="txtName" class="w200" value="<?php echo $userBilling["zipcode"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_city"] ?></label><input name="billing_city" type="text" id="txtName" class="w200" value="<?php echo $userBilling["city"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_phone"] ?></label><input name="billing_phone" type="text" id="txtName" class="w200" value="<?php echo $userBilling["phone"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_fax"] ?></label><input name="billing_fax" type="text" id="txtName" class="w200" value="<?php echo $userBilling["fax"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_tax"] ?></label><input name="billing_ustid" type="text" id="txtName" class="w200" value="<?php echo $userBilling["ustid"] ?>" /></li><li class="clear"></li>
	
	<li><label for="name"><?php echo $sLang["userdetails"]["main_country"] ?></label>
	
	<select name="billing_country">
	<?php
	$query = mysql_query("SELECT countryname, id FROM s_core_countries ORDER BY position");
	while ($country=mysql_fetch_array($query)){
		if ($country["id"]==$userBilling["countryID"]) $sel = "selected"; else $sel = "";
		echo "<option value=\"{$country["id"]}\" $sel>".$country["countryname"]."</option>";
	}
	?>
	</select>
	<li class="clear"></li>
	<li><label for="birthday">Geburtsdatum</label>
		
	<?php
		if($userBilling["birthday"] != "1970-01-01" && $userBilling["birthday"] != "0000-00-00")
		{
			$birthday_conf = explode("-", $userBilling["birthday"]);
			$cDay = $birthday_conf[2];
			$cMonth = $birthday_conf[1];
			$cYear = $birthday_conf[0];
		}
	?>
	<select style="width: 60px;" name="birthday">
	<option>--</option>	
		<?php 
			for($i=1; $i<=31; $i++)
			{
				if($i == $cDay)
				{
					echo sprintf("<option value='%s' selected>%s</option>", $i, $i);
				}else{
					echo sprintf("<option value='%s'>%s</option>", $i, $i);
				}				
			}
		?>		
	</select>

<select style="width: 60px;" name="birthmonth">
<option>-</option>	
		<?php 
			for($i=1; $i<=12; $i++)
			{
				if($i == $cMonth)
				{
					echo sprintf("<option value='%s' selected>%s</option>", $i, $i);
				}else{
					echo sprintf("<option value='%s'>%s</option>", $i, $i);
				}
			}
		?>	
	</select>

<select style="width: 60px;" name="birthyear">
<option>----</option>		
		<?php 
			$year = date("Y", time());
			for($i=1900; $i<=($year-10); $i++)
			{
				if($i == $cYear)
				{
					echo sprintf("<option value='%s' selected>%s</option>", $i, $i);
				}else{
					echo sprintf("<option value='%s'>%s</option>", $i, $i);
				}
			}
		?>	
	</select>
	</li><li class="clear"></li>
	</ul>
</fieldset>

<div style="clear:both;"></div>	

<fieldset style="margin-left:10px; margin-right:10px; position:relative;">
<legend><?php echo $sLang["userdetails"]["main_free_text"] ?></legend>
	<ul>
	<li>
	<label for="name"><?php echo $sLang["userdetails"]["main_field_1"] ?></label><input name="billing_text1" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text1"] ?>" /></li><li class="clear">
	</li>
	<li>
	<label for="name"><?php echo $sLang["userdetails"]["main_field_2"] ?></label><input name="billing_text2" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text2"] ?>" /></li><li class="clear">
	</li>
<li>
<label for="name"><?php echo $sLang["userdetails"]["main_field_3"] ?></label><input name="billing_text3" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text3"] ?>" /></li>
<li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_field_4"] ?></label><input name="billing_text4" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text4"] ?>" /></li><li class="clear">
	</li>
	<li>
	<label for="name"><?php echo $sLang["userdetails"]["main_field_5"] ?></label><input name="billing_text5" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text5"] ?>" /></li><li class="clear">
	</li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_field_6"] ?></label><input name="billing_text6" type="text" id="txtName" class="w200" value="<?php echo $userBilling["text6"] ?>" /></li>
	<li class="clear"></li>
	</ul>
	
	
	
	
</fieldset>

<div class="clear" style="height:10px;display:block; position:static;width:200px;"></div>
	<!-- main_adress -->
<fieldset style="margin-left:10px; margin-right:10px; float:none; height:360px;">
	<legend><?php echo $sLang["userdetails"]["main_Address"] ?></legend>
	<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>" />
  	<input type="hidden" name="saveShipping" value="1" />
	
	
	<ul>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_Title"] ?></label>
	<select name="salutation">
	<option value="mr" <?php echo $userShipping["salutation"]=="mr" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_mr"] ?></option>
	<option value="ms" <?php echo $userShipping["salutation"]=="ms" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_ms"] ?></option>
	<option value="company" <?php echo $userShipping["salutation"]=="company" ? "selected" : ""?>><?php echo $sLang["userdetails"]["main_company"] ?></option>
	</select>
	</li>
	<li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_company_1"] ?></label><input name="company" type="text" id="txtName" class="w200" value="<?php echo $userShipping["company"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_Department"] ?></label><input name="department" type="text" id="txtName" class="w200" value="<?php echo $userShipping["department"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_firstname"] ?></label><input name="firstname" type="text" id="txtName" class="w200" value="<?php echo $userShipping["firstname"] ?>" /></li><li class="clear"></li>
	
	<li><label for="name"><?php echo $sLang["userdetails"]["main_lastname"] ?></label><input name="lastname" type="text" id="txtName" class="w200" value="<?php echo $userShipping["lastname"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_street"] ?></label><input name="street" type="text" id="txtName" class="w200" value="<?php echo $userShipping["street"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_house"] ?></label><input name="streetnumber" type="text" id="txtName" class="w200" value="<?php echo $userShipping["streetnumber"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_Postal_Code"] ?></label><input name="zipcode" type="text" id="txtName" class="w200" value="<?php echo $userShipping["zipcode"] ?>" /></li><li class="clear"></li>
	<li><label for="name"><?php echo $sLang["userdetails"]["main_city"] ?></label><input name="city" type="text" id="txtName" class="w200" value="<?php echo $userShipping["city"] ?>" /></li><li class="clear"></li>	
	
	<li><label for="name"><?php echo $sLang["userdetails"]["main_country"] ?></label>
	<select name="country">
	<?php
	$query = mysql_query("SELECT countryname, id FROM s_core_countries ORDER BY position");
	while ($country=mysql_fetch_array($query)){
		if ($country["id"]==$userShipping["countryID"]) $sel = "selected"; else $sel = "";
		echo "<option value=\"{$country["id"]}\" $sel>".$country["countryname"]."</option>";
	}
	?>
	</select></li></ul>
	
</fieldset>

<div class="clear" style="height:10px;display:block;width:200px;"></div>	

<fieldset style="margin-left:10px; margin-right:10px; float:none;position:static;">
<legend><?php echo $sLang["userdetails"]["main_free_text"] ?></legend>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_1"] ?></label><input name="text1" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text1"] ?>" /></li><li class="clear"></li></ul>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_2"] ?></label><input name="text2" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text2"] ?>" /></li><li class="clear"></li></ul>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_3"] ?></label><input name="text3" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text3"] ?>" /></li><li class="clear"></li></ul>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_4"] ?></label><input name="text4" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text4"] ?>" /></li><li class="clear"></li></ul>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_5"] ?></label><input name="text5" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text5"] ?>" /></li><li class="clear"></li></ul>
	<ul><li><label for="name"><?php echo $sLang["userdetails"]["main_field_6"] ?></label><input name="text6" type="text" id="txtName" class="w200" value="<?php echo $userShipping["text6"] ?>" /></li><li class="clear"></li></ul>
</fieldset>

<div style="clear:both;"></div>	

<fieldset style="margin-left:10px; margin-right:10px; position:relative;">
	<legend><?php echo $sLang["userdetails"]["main_selected_Payment"] ?></legend>
		<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>" />
	  	<input type="hidden" name="savePayment" value="1" />
	  	
	  	<?php
			// Alle Zahlungsarten auslesen ...
			$getPaymentMeans = mysql_query("
			SELECT * FROM
			s_core_paymentmeans ORDER BY name ASC
			");
			
			
			while ($paymentMean = mysql_fetch_array($getPaymentMeans)){
				if ($paymentMean["table"]){
					$table = $paymentMean["table"];
					$showColumns = mysql_query("
					SHOW COLUMNS FROM $table
					");
					while ($field = mysql_fetch_assoc($showColumns)){
						$fields[] = $field["Field"];
					}
					// Read existing data from table
					$getTableData = mysql_query("
					SELECT * FROM $table WHERE userID = {$_GET["id"]}
					");
					$getTableData = mysql_fetch_assoc($getTableData);
				}else {
					unset($fields);
				}
	  		$active = $paymentMean["active"] ? "Aktiv" : "Inaktiv";
		?>
	  	
	  	<ul><li><label for="name" style="width:200px;"><?php if ($paymentMean["active"]){ ?><span style="color:#008000"><?php } else { ?><span style="color:#F00"><?php } ?><?php echo preg_replace("/(.*)\((.*)\)(.*)/","\\1",$paymentMean["description"]) ?>(<?php echo $active ?>):</span></label><input name="paymentType" type="radio" value="<?php echo $paymentMean["id"] ?>" <?php echo $userMain["paymentID"]==$paymentMean["id"] ? "checked" : ""?> /></li><li class="clear"></li>
	  	<?php
	  	if (count($fields)){
	  	?>
	  	<li><table style="margin-left:150px">
	  	<?php
			foreach ($fields as $field){
				if ($field=="id" || $field=="userID") continue;
				?>
				<tr>
				<td><?php echo ucfirst($field) ?></td>
				<td><input type="text" name="_PAYMENT[<?php echo $paymentMean["id"] ?>][<?php echo $field ?>]" value="<?php echo $getTableData[$field]?>" /></td>
				</tr>
				<?php
			}
	  	?>
	  	</table></li><li class="clear"></li>
	  	<?php
	  	}
	  	?>
	  	</ul>
		<?php
		}
		?>
	  	

</fieldset>
</form>

<?php if($_REQUEST['ext'] == 1) { ?>


	<div class="buttons" id="buttons" style="margin-left:10px;">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('mainsave').submit();" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button></li>	
				
		</ul>
		</div>
<?php } ?>
<div style="clear:both;"></div>

</body>
</html>