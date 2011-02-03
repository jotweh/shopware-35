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

if(empty($_GET['orderId']))
	die("Missing Param: orderId");

$orderId = intval($_GET['orderId']);

$query = mysql_query("SELECT userID FROM s_order WHERE id = $orderId");
if (!mysql_num_rows($query)){
	die("Order error:".mysql_error($query));
}

$userID = mysql_result($query,0,"userID");

// Query all customer-groups
$getSubshops = mysql_query("
SELECT id, name FROM s_core_multilanguage
");
//$userMain["customergroup"] = 1;

if ($_POST["savePayment"]){

	$updateUser = mysql_query("UPDATE s_order SET paymentID={$_POST["paymentType"]} WHERE id=$orderId");
	
	$sSuccess = $sLang["userdetails"]["main_change_payment"];
	
	if (count($_POST["_PAYMENT"])){
		
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
					SELECT id FROM $table WHERE userID = $userID
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
						WHERE userID=$userID
						";
						//echo $sql;
						$updateSQL = mysql_query($sql);
					}else {
						// insert
						$values[] = "userID";
						$data2[] = $userID;
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

if ($_POST["saveBilling"]){
	
	$sql = "
	UPDATE s_order_billingaddress SET 
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
	text6='{$_POST["billing_text6"]}'
	WHERE orderID={$orderId}
	";
	
	$updateUser = mysql_query($sql);
	
	if ($updateUser){
		$sSuccess = $sLang["userdetails"]["main_changes_saved"];
	}else {
		$sError = $sLang["userdetails"]["main_changes_not_saved"];
	}
	

}if ($_POST["saveShipping"]){

	$checkShipping = mysql_query("
	SELECT id FROM s_order_shippingaddress WHERE orderID='{$orderId}'
	");
	
	if (@mysql_num_rows($checkShipping)){
		// Already there, shipping-adress just needed to get updated
		
		$sql = "
		UPDATE s_order_shippingaddress SET 
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
		WHERE orderID='{$orderId}'
		";

	}else {
		// New shipping-adress,just insert
		$sql = "
		INSERT INTO s_order_shippingaddress
		(userID, orderID company,department,salutation, firstname, lastname, street, streetnumber,
		zipcode, city,countryID)
		VALUES (
		$userID,
		{$orderId},
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


if(!empty($_POST['sSubshopId']))
{
	mysql_query("UPDATE `s_order` SET `subshopID` = '".intval($_POST['sSubshopId'])."' WHERE `id` = '{$orderId}' LIMIT 1");
}





$queryOrderMain = mysql_query("
	SELECT subshopID, ordernumber,paymentID FROM s_order WHERE id='{$orderId}'
");
$subshopID = mysql_result($queryOrderMain, 0, "subshopID");
$ordernumber = mysql_result($queryOrderMain, 0, "ordernumber");
$paymentID = mysql_result($queryOrderMain, 0, "paymentID");

$queryUserBillingAddress = mysql_query("
SELECT * FROM s_order_billingaddress WHERE orderID={$orderId}
");
if (!mysql_num_rows($queryUserBillingAddress)) die($sLang["userdetails"]["main_could_not_fetch_billing-adress"]);

$userBilling = mysql_fetch_array($queryUserBillingAddress);

$queryUserShippingAddress = mysql_query("
SELECT * FROM s_order_shippingaddress WHERE orderID={$orderId}
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["userdetails"]["main_should_the_customer"]." ".$userBilling["firstname"]." ".$userBilling["lastname"]." ".$sLang["userdetails"]["main_really_be_deleted"] ?>',window,'deleteClient',ev);
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
	
<form id="mainsave" name="save" method="post" action="<?php echo basename(__FILE__)."?orderId=".$_GET["orderId"]; ?>" onSubmit="return precheckMain()">
	<div style="height:10px;"></div>
	

<fieldset style="margin-left:10px; margin-right:10px; position:relative; clear:both;float:none;">
<legend>Hinweis</legend>
	<div>
		Anpassungen über das folgende Formular, haben lediglich Auswirkung auf die Bestellung (Bestellnummer: <?php echo $ordernumber; ?>)
	</div>
</fieldset>
</script>

<div style="clear:both;"></div>
	
<?php
if(mysql_num_rows($getSubshops) > 1)
{
?>
	<fieldset style="margin-left:10px; margin-right:10px; position:relative; clear:both;float:none;">
		<legend>Subshop-Einstellungen</legend>
		
		<ul>
		<li><label for="name" style="width:110px">Subshop</label>
		<select name="sSubshopId">
		<?php
		while ($subshopData=mysql_fetch_array($getSubshops)){
		?>
			<option <?php echo $subshopID==$subshopData["id"] ? "selected" : "" ?> value="<?php echo $subshopData["id"]?>"><?php echo $subshopData["name"] ?></option>
		<?php
		}
		?>
		</select>
		</li><li class="clear"></li>
		</ul>
		
		</div>
		
		</div>
	</fieldset>
	</script>
	
	<div style="clear:both;"></div>
	
	
	<div class="buttons" id="buttons" style="margin-left:10px;">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('mainsave').submit();" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button></li>	
		
			
		
		</ul>
		</div>
	
	
	<div style="clear:both;"></div>
<?php
} //-- subshop option
?>
	
	
<!--<form id="billingsave" name="save" method="post" action="<?php echo $PHP_SELF."?id=".$_GET["id"] ?>" onSubmit="return precheckMain()">-->
<fieldset style="margin-left:10px; margin-right:10px; position:relative; clear:both;float:none;">
	<legend>Rechnungsadresse:</legend>

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


<div style="clear:both;"></div>	

<fieldset style="margin-left:10px; margin-right:10px; position:relative;">
	<legend><?php echo $sLang["userdetails"]["main_selected_Payment"] ?></legend>
		<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>" />
	  	<input type="hidden" name="savePayment" value="1" />
	  	
	  	<?php
			// Alle Zahlungsarten auslesen ...
			$getPaymentMeans = mysql_query("
			SELECT * FROM
			s_core_paymentmeans ORDER BY active DESC,name ASC
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
					SELECT * FROM $table WHERE userID = $userID
					");
					$getTableData = mysql_fetch_assoc($getTableData);
				}else {
					unset($fields);
				}
	  		$active = $paymentMean["active"] ? "Aktiv" : "Inaktiv";
		?>
	  	
	  	<ul><li><label for="name" style="width:200px;"><?php if ($paymentMean["active"]){ ?><span style="color:#008000"><?php } else { ?><span style="color:#F00"><?php } ?><?php echo preg_replace("/(.*)\((.*)\)(.*)/","\\1",$paymentMean["description"]) ?>(<?php echo $active ?>):</span></label><input name="paymentType" type="radio" value="<?php echo $paymentMean["id"] ?>" <?php echo $paymentID==$paymentMean["id"] ? "checked" : ""?> /></li><li class="clear"></li>
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


<div class="buttons" id="buttons" style="margin-left:10px;">
<ul>
<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('mainsave').submit();" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button></li>	
		
</ul>
</div>
		
<div style="clear:both;"></div>

</body>
</html>