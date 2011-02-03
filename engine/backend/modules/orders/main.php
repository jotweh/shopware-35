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
?>
<?php
if (!$_GET["id"]) die ($sLang["orders"]["main_no_order_given"]);

if ($_GET["transfer"]){
	// Query user - id
	$queryUser = mysql_query("
	SELECT userID FROM s_order WHERE id = {$_GET["id"]}
	");
	$userID = mysql_fetch_assoc($queryUser); $userID = $userID["userID"];
	if (empty($userID)){
		die("Der zugeordnete Benutzer wurde zwischenzeitlich gelöscht");
	}
	// Umwandeln der Bestellung
	// Setting ordernumber 
	mysql_query("UPDATE s_order_number SET number=number+1 WHERE name='invoice'");
	$orderNumber = mysql_query("SELECT number FROM s_order_number WHERE name='invoice'");
	$orderNumber = mysql_result($orderNumber,0,"number");
	if (empty($orderNumber)) die("Bestellnummer konnte nicht ermittelt werden");
	$updateOrder = mysql_query("
	UPDATE s_order SET ordernumber = '$orderNumber', status=1 WHERE id = {$_GET["id"]}
	");
	$updateOrderDetails = mysql_query("
	UPDATE s_order_details SET ordernumber = '$orderNumber' WHERE orderID = {$_GET["id"]}
	");
	if ($updateOrder && $updateOrderDetails){
		// Insert Billing & Shipping Address
		$getBilling = mysql_query("
		SELECT * FROM s_user_billingaddress WHERE userID=$userID
		");
		$getBilling = mysql_fetch_assoc($getBilling);
		if (empty($getBilling)){
			die("s_user_billingaddress - relation failure -");
		}
		$sql = "
		INSERT INTO s_order_billingaddress
		(
		userID,
		orderID,
		company,
		department,
		salutation,
		customernumber,
		firstname,
		lastname,
		street,
		streetnumber,
		zipcode,
		city,
		phone,
		fax,
		countryID,
		ustid,
		text1,
		text2,
		text3,
		text4,
		text5,
		text6
		)
		VALUES (
		$userID,
		{$_GET["id"]},
		'{$getBilling["company"]}',
		'{$getBilling["department"]}',
		'{$getBilling["salutation"]}',
		'{$getBilling["customernumber"]}',
		'{$getBilling["firstname"]}',
		'{$getBilling["lastname"]}',
		'{$getBilling["street"]}',
		'{$getBilling["streetnumber"]}',
		'{$getBilling["zipcode"]}',
		'{$getBilling["city"]}',
		'{$getBilling["phone"]}',
		'{$getBilling["fax"]}',
		'{$getBilling["countryID"]}',
		'{$getBilling["ustid"]}',
		'{$getBilling["text1"]}',
		'{$getBilling["text2"]}',
		'{$getBilling["text3"]}',
		'{$getBilling["text4"]}',
		'{$getBilling["text5"]}',
		'{$getBilling["text6"]}'
		)
		";
		
		$insertBilling = mysql_query($sql);
		
		$getShipping = mysql_query("
		SELECT * FROM s_user_shippingaddress WHERE userID=$userID
		");
		$getShipping = mysql_fetch_assoc($getShipping);
		if (empty($getShipping)){
			$getShipping = $getBilling;
		}
		$sql = "
		INSERT INTO s_order_shippingaddress
		(
		userID,
		orderID,
		company,
		department,
		salutation,
		firstname,
		lastname,
		street,
		streetnumber,
		zipcode,
		city,
		countryID,
		text1,
		text2,
		text3,
		text4,
		text5,
		text6
		)
		VALUES (
		$userID,
		{$_GET["id"]},
		'{$getShipping["company"]}',
		'{$getShipping["department"]}',
		'{$getShipping["salutation"]}',
		'{$getShipping["firstname"]}',
		'{$getShipping["lastname"]}',
		'{$getShipping["street"]}',
		'{$getShipping["streetnumber"]}',
		'{$getShipping["zipcode"]}',
		'{$getShipping["city"]}',
		'{$getShipping["countryID"]}',
		'{$getShipping["text1"]}',
		'{$getShipping["text2"]}',
		'{$getShipping["text3"]}',
		'{$getShipping["text4"]}',
		'{$getShipping["text5"]}',
		'{$getShipping["text6"]}'
		)
		";
		
		$insertShipping = mysql_query($sql);
		
	}else {
		echo mysql_error()."###";
	}
}


if(isset($_POST["invoice_shipping"]))
	$_POST["invoice_shipping"] = floatval(str_replace(',', '.',$_POST["invoice_shipping"]));

if ($_GET["id"] && $_POST["saveMain"]){
	$oldAmount = mysql_query("
		SELECT invoice_shipping, invoice_amount, currencyFactor FROM s_order WHERE id={$_GET["id"]}
	");
	$oldAmount = mysql_fetch_array($oldAmount);
	
	/*
	Multilanguage save recalc of shippingcosts/total-amount
	*/
	if (empty($oldAmount["currencyFactor"])) $oldAmount["currencyFactor"] = 1;
	$_POST["invoice_shipping"] = round($_POST["invoice_shipping"]*$oldAmount["currencyFactor"],2);
	
	$newAmount = ($oldAmount['invoice_amount']-$oldAmount['invoice_shipping'])+$_POST["invoice_shipping"];
	
	if ($_POST["cleareddate"]){
		$cleareddate = explode(".",$_POST["cleareddate"]);
		$cleareddate = $cleareddate[2]."-".$cleareddate[1]."-".$cleareddate[0];
	}
	$_POST["internalcomment"] = htmlentities($_POST["internalcomment"]);
	
	$updateOrder = mysql_query("
	UPDATE s_order SET comment='{$_POST["comment"]}',customercomment='{$_POST["customercomment"]}',internalcomment='{$_POST["internalcomment"]}', invoice_amount=$newAmount, status='{$_POST["statusMain"]}',cleared='{$_POST["statusPayment"]}', invoice_shipping='{$_POST["invoice_shipping"]}', cleareddate='$cleareddate', trackingcode='{$_POST["trackingcode"]}' WHERE id={$_GET["id"]}
	");
	
}
$queryOrder = mysql_query("
SELECT s_order.id, remote_addr,s_core_multilanguage.name,o_attr1,o_attr2,o_attr3,o_attr4,o_attr5,o_attr6,userID,currency,currencyFactor, invoice_shipping, ordernumber, referer,language,comment,customercomment, invoice_amount,paymentID, DATE_FORMAT(ordertime,'%d.%m.%Y %H:%i') AS ordertime,DATE_FORMAT(cleareddate,'%d.%m.%Y') AS cleareddate, status, cleared, trackingcode, dispatchID,internalcomment FROM s_order 
LEFT JOIN s_core_multilanguage ON s_core_multilanguage.id = s_order.subshopID
WHERE s_order.id={$_GET["id"]}
");


if (!@mysql_num_rows($queryOrder)) die($sLang["orders"]["main_order_not"]."{$_GET["id"]} ".$sLang["orders"]["main_order_not_found"]);

$orderMain = mysql_fetch_array($queryOrder);




$userMain = mysql_query("
SELECT * FROM s_user WHERE id={$orderMain["userID"]}
");

if (!@mysql_num_rows($userMain)) echo $sLang["orders"]["main_Attention_assigned_user_was_deleted"]."<br />";
$userMain = mysql_fetch_array($userMain);

// Fetch User-Details
// Billingadress and Shippingadress

$userGetBilling = mysql_query("
SELECT * FROM s_order_billingaddress WHERE userID={$orderMain["userID"]} AND orderID={$_GET["id"]}
");

if (!@mysql_num_rows($userGetBilling) && !empty($orderMain["ordernumber"])) echo $sLang["orders"]["main_Attention_assigned_user_was_deleted"]."<br />";

$userGetShipping = mysql_query("
SELECT * FROM s_order_shippingaddress WHERE userID={$orderMain["userID"]} AND orderID={$_GET["id"]}
");

$userGetBilling = mysql_fetch_array($userGetBilling);

if (!@mysql_num_rows($userGetShipping)) $userGetShipping = $userGetBilling; else $userGetShipping = mysql_fetch_array($userGetShipping);




if ($_POST["saveBilling"]){
	
	
	if ($updateUser){
		$sSuccess = $sLang["orders"]["main_changes_saved"];
	}else {
		$sError = $sLang["orders"]["main_changes_save_failed"];
	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sLang["orders"]["main_search"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />


<script type="text/javascript">
var activePanel;
</script>

</head>
<style type="text/css">
.toggler {
	color: #FFF;
	margin: 0;
	height: 22px;
	padding: 7px 0px 3px 40px;
	background: url(../../../backend/img/default/window/bg_toggle_disabled.gif) repeat-x;
	border-bottom: 1px solid #ddd;
	border-right: 1px solid #ddd;
	border-top: 1px solid #f5f5f5;
	border-left: 1px solid #f5f5f5;
	font-size: 11px;
	font-weight: bold;
	font-family: arial;
	cursor:pointer;
}
.element {
 background: url(../../../backend/img/default/window/bg_toggle_gradient.gif) repeat-x;
}
 
.element p {
	margin: 0;
	padding: 4px;
}
 
.float-right {
	padding:10px 20px;
	float:right;
}
blockquote {
	text-style:italic;
	padding:5px 0 5px 30px;
}
.clear {
	clear: both;
	padding: 0;
	margin:0;
	width: 0px;
	height: 0px;
	line-height: 0px;
	font-size: 0px;
}
#table select {
	padding: 0px;
	height: 16px;
	width: 80px;
	font-size:10px;
}
fieldset {
	margin: 5px 0px;
}
.mootable .tbody table tr td div{
	overflow: visible;
}
.mootable input {
	padding: 0px 3px;
	text-align: right;
	height: 12px;
	width: 50px;
}
.mootable option {
	padding: 0px 3px;
	height: 12px; width: 60px;
	font-size: 10px;
}
.mootable select {
	font-size: 10px;
}
.mootable fieldset {
	margin: 5px 0px;
}
label {
	text-align:left;
}
</style>
<body style="padding-top:0px;">
<?php
if (empty($orderMain["ordernumber"])){
	echo "Diese Bestellung wurde vom Kunden abgebrochen<br />Klicken Sie auf \"Bestellung umwandeln\" um diese in eine normale Bestellung umzuwandeln!<br />
	<strong>Achtung! Die umgewandelte Bestellung wird automatisch mit der aktuell hinterlegten Anschrift des Kunden verknüpft!</strong>
	<br /><br />";
?>
<form method="POST" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_REQUEST["id"]?>&transfer=1">
<div class="buttons" id="buttons">
	<ul>
	<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
	<button type="submit" value="send" class="button"><div class="buttonLabel">Bestellung umwandeln</div></button>
	</li>	
	</ul>
</div>
</form>
<?php
	exit;
}
?>
<script language="javascript">
<?php if ($sError){?>
	parent.parent.parent.Growl('<?php echo $sError; ?>');
	parent.parent.parent.sWindows.focus.shake(15);
<?php }?>
<?php if ($sSuccess){?>
	parent.parent.parent.Growl('<?php echo $sSuccess ?>');
<?php }?>	
</script>

<!--<div style="border:1px solid #bebebe;">
<div id="accordion">

	<h3 class="toggler atStart">
		< ? = $sLang["orders"]["main_General_data"] ?>
	</h3>
	<div class="element atStart" id="main">-->
	<!-- Maindata -->
<div style="display:block";>		
	
			
		<fieldset style="width:20%; min-width:150px; float:left; margin-right:10px;">
			<legend><?php echo $sLang["orders"]["main_Billing_address"] ?></legend>
			
			<?php echo $userGetBilling["company"] ? $userGetBilling["company"]."<br />" : "" ?>
			<?php echo $userGetBilling["firstname"]." ".$userGetBilling["lastname"]?><br />
			<?php echo $userGetBilling["street"]." ".$userGetBilling["streetnumber"] ?><br />
			<?php echo $userGetBilling["zipcode"]." ".$userGetBilling["city"] ?><br />
			<?php
			$sql = "SELECT countryname FROM s_core_countries WHERE id={$userGetBilling["countryID"]}";
			$queryCountry = mysql_query($sql);
			$country =  mysql_result($queryCountry,0,"countryname");
			echo $country;
			?><a class="ico pencil" style="cursor:pointer" onclick="parent.parent.parent.loadSkeleton('userdetails',false,{'user': <?php echo $orderMain["userID"] ?>})"></a>
		</fieldset>
			
		<?php
		$ub = $userGetBilling;
		$us = $userGetShipping;
		
		if (
		$ub["company"]!=$us["company"]
		||
		$ub["lastname"]!=$us["lastname"]
		||
		$ub["street"]!=$us["street"]
		||
		$ub["streetnumber"]!=$us["streetnumber"]
		||
		$ub["zipcode"]!=$us["zipcode"]
		){
			$addressesDiffers = true;	
			
		}
		?>
			
		<fieldset style="width:20%; min-width:150px; float:left; margin-right:10px;">
			<legend><?php echo $sLang["orders"]["main_Delivery_address"] ?> <?php echo $addressesDiffers ? "<span style=\"color:#ff0000;\">(abweichend)</span>" : ""?></legend>
			<div <?php echo $addressesDiffers ? "style=\"border: 1px;\"" : ""?>>
				<?php echo $userGetShipping["company"] ? $userGetShipping["company"]."<br />" : "" ?>
				<?php echo $userGetShipping["firstname"]." ".$userGetShipping["lastname"]?><br />
				<?php echo $userGetShipping["street"]." ".$userGetShipping["streetnumber"] ?><br />
				<?php echo $userGetShipping["zipcode"]." ".$userGetShipping["city"] ?><br />
				<?php
				$sql = "SELECT countryname FROM s_core_countries WHERE id={$userGetShipping["countryID"]}";
				$queryCountry = mysql_query($sql);
				$country =  mysql_result($queryCountry,0,"countryname");
				echo $country;
				?><a class="ico pencil" style="cursor:pointer" onclick="parent.parent.parent.loadSkeleton('userdetails',false,{'user': <?php echo $orderMain["userID"] ?>})"></a>
			</div>
		</fieldset>
				
		<fieldset style="width:20%; min-width:150px; float:left; margin-right:10px;">
			<legend><?php echo $sLang["orders"]["main_payment"] ?></legend>
			 
			<?php
				$queryPayment = mysql_query("SELECT description FROM s_core_paymentmeans WHERE id={$orderMain["paymentID"]}");
				if (@mysql_num_rows($queryPayment)){
					echo mysql_result($queryPayment,0,"description");
				}else {
					echo $sLang["orders"]["main_payment_not_found"];
				}
			?><a class="ico pencil" style="cursor:pointer" onclick="parent.parent.loadSkeleton('userdetails',false,{'user': <?php echo $orderMain["userID"] ?>})"></a>
		</fieldset>
		
		<div style="clear:both;"></div>	
		<form>
		<div class="buttons" id="buttons">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-top:15px;">
			<button type="submit" value="send" class="button" onclick="parent.parent.parent.loadSkeleton('userdetails',false,{'orderId': <?php echo $_GET["id"] ?>}); return false;"><div class="buttonLabel">Bestelldaten ändern</div></button>
			</li>	
			</ul>
		</div>
		</form>
		<form id="mainsave" name="save" method="post" action="<?php echo $PHP_SELF."?id=".$_GET["id"] ?>">
		<input type="hidden" name="saveEdit" value="<?php echo $edit; ?>">
		<input type="hidden" name="saveMain" value="1">
		<div style="clear:both;"></div>	
		<?php
		// Default Sprache / Währung auslesen
		$defaultLanguage = "de";
		$defaultCurrency = mysql_query("
		SELECT currency FROM s_core_currencies WHERE standard  = 1 LIMIT 1
		");
		$defaultCurrency = @mysql_result($defaultCurrency,0,"currency");
		?>
		<fieldset>
			<legend><?php echo $sLang["orders"]["main_orderdetails"] ?></legend>
			<?php if ($orderMain["referer"]){?><strong>Herkunft:</strong> <?php echo strip_tags(htmlentities(($orderMain["referer"])))?><br /><?php } ?>
			<?php if ($orderMain["language"]!=$defaultLanguage) { ?><a class="ico information"></a><?php } ?>
			<?php if ($orderMain["name"]){?><strong>Shop:</strong> <?php echo strip_tags($orderMain["name"])?><br /><?php } ?>
			<?php if ($orderMain["language"]){?><strong>Sprache:</strong> <?php echo strip_tags($orderMain["language"])?><br /><?php } ?>
			<strong><?php echo $sLang["orders"]["main_time"] ?></strong> <?php echo $orderMain["ordertime"]?><br />
			<strong><?php echo $sLang["orders"]["main_ordernumber"] ?></strong> <?php echo $orderMain["ordernumber"] ?><br/>
			<strong>IP-Adresse: </strong><?php echo $orderMain["remote_addr"] ?><br />
			<?php
			for ($i = 1;$i <= 6;$i++){
			?>
			<strong>Freitext <?php echo $i ?>: </strong><?php echo $orderMain["o_attr".$i] ?><br />
			<?php
			}
			?>
			<?php if ($orderMain["currency"]!=$defaultCurrency) { ?><a class="ico information"></a><?php } ?>
			<strong><?php echo $sLang["orders"]["main_Currency"] ?> </strong> <?php echo $orderMain["currency"] ?><br />
			<strong><?php echo $sLang["orders"]["main_Total"] ?></strong> 
			<?php echo $sCore->sFormatPrice(round($orderMain["invoice_amount"]/$orderMain["currencyFactor"],2)) ?> <?php echo $sCore->sGetCurrencyChar(); ?>
			<?php
				if ($orderMain["currencyFactor"]!=1){
			?>
			<?php echo "<strong>( ".$orderMain["currency"]." ".$sCore->sFormatPrice(round($orderMain["invoice_amount"],2))." )</strong>";?> 
			<?php
				}
			?><br />
			<strong><?php echo $sLang["orders"]["main_Elected_Dispatch"] ?></strong> <?php 

			  if (!empty($sCore->sCONFIG['sPREMIUMSHIPPIUNG']))
			  {
			  	$dispatch_table = 's_premium_dispatch';
			  }
			  else
			  {
			  	$dispatch_table = 's_shippingcosts_dispatch';
			  }
			$queryDispatch = mysql_query("SELECT name FROM $dispatch_table WHERE id = {$orderMain["dispatchID"]}");

			
			if (@mysql_num_rows($queryDispatch)){
				echo mysql_result($queryDispatch,0,"name");
			}else {
				echo "<p style=\"color:#F00\">".$sLang["orders"]["main_Dispatch_not_saved"]."</p>";
			}
			
			if($orderMain["cleareddate"] == "00.00.0000")
			{
				$orderMain["cleareddate"] = "";
			}
			?><br /><br />
			
			<script type="text/javascript">
			// Bezahlt am ExtJs
			Ext.onReady(function(){
				new Ext.form.DateField({
					id:'cleareddate',
					width:208,
					format: 'd.m.Y',
					value: '<?php echo $orderMain["cleareddate"] ?>',
					renderTo: 'main_Paid_on'
				});
			});	
			</script>
			
			<ul>
			<!-- Bezahlt am -->
			<li><label for="invoice_shipping"><?php echo $sLang["orders"]["main_Paid_on"] ?></label>
			
			<div id="main_Paid_on" style="float:left;"></div>
			<!--<input name="cleareddate" style="width:70px; height:25px; padding: 0px 3px;" class="" 
			value="<?php echo $orderMain["cleareddate"] ?>" id="cleareddate" 
			onClick="displayDatePicker('cleareddate', false, 'dmy', '.');">
			<a class="ico calendar" onClick="displayDatePicker('cleareddate', false, 'dmy', '.');"></a>-->
			<br />
			</li><li class="clear"/>
			<!-- Tracking-Code -->
			<li><label for="invoice_shipping"><?php echo $sLang["orders"]["main_Tracking-Code"] ?></label>
			<input name="trackingcode" style="width:200px; height:25px; padding: 0px 3px;"class="" value="<?php echo $orderMain["trackingcode"] ?>"><br />
			</li><li class="clear"/>
			
			<li><label for="invoice_shipping"><?php echo $sLang["orders"]["main_forwarding_charges"] ?></label>
			<input name="invoice_shipping" style="text-align:right; width:50px; padding: 0px 3px;" value="<?php if(!empty($orderMain["invoice_shipping"])){ echo $sCore->sFormatPrice(round($orderMain["invoice_shipping"]/$orderMain["currencyFactor"],2)); } else { echo "0,00"; }?>">
			<?php echo " &nbsp;".$sCore->sGetCurrencyChar()." &nbsp;"; ?>
			<?php if ($orderMain["currencyFactor"]!=1 && !empty($orderMain["invoice_shipping"])){ ?>
			<strong>(<?php echo $orderMain["currency"]  ?> <?php echo $sCore->sFormatPrice(round($orderMain["invoice_shipping"],2)); ?>)</strong>
			<?php
			}
			?>
			<br />
			</li><li class="clear"/>
			<li><label for="name"><?php echo $sLang["orders"]["main_order_status"] ?></label><select name="statusMain" class="w200">
			<?php
			$getAllStates = mysql_query("
				SELECT id, description FROM s_core_states WHERE
				`group` = 'state'
				ORDER BY position ASC
			");
			while ($state = mysql_fetch_assoc($getAllStates)){		
				if ($state["id"]==$orderMain["status"]){
					$selected = "selected";
				}else {
					$selected = "";
				}
				echo "<option value=\"{$state["id"]}\" $selected>{$state["description"]}</option>";
			}
			?>
			</select></li><li class="clear"/>
			
			<li><label for="name"><?php echo $sLang["orders"]["main_payment_status"] ?></label><select name="statusPayment" class="w200">
			<?php
			$getAllStates = mysql_query("
				SELECT id, description FROM s_core_states WHERE
				`group` = 'payment'
				ORDER BY position ASC
			");
			while ($state = mysql_fetch_assoc($getAllStates)){
				if (!$orderMain["cleared"]) $orderMain["cleared"] = 17;
				if ($state["id"]==$orderMain["cleared"]){
					$selected = "selected";
				}else {
					$selected = "";
				}
				echo "<option value=\"{$state["id"]}\" $selected>{$state["description"]}</option>";
			}
			?>
			</select></li><li class="clear"/>
			</ul>
			<ul>
			<label style="width:400px;font-weight:bold;color:#000"><strong>Interner Kommentar! Wird nicht ausgegeben!</strong></label>
			</li><li class="clear"/>
			<li><label for="name">Interner Kommentar:</label><textarea name="internalcomment" class="w200" rows="8" style="width:300px"><?php echo $orderMain["internalcomment"]?></textarea></li><li class="clear"/>
			<li>
			<label style="width:400px;font-weight:bold;color:#000"><strong>Achtung! Die nachfolgenden Kommentare sind für den Kunden sichtbar!</strong></label>
			</li><li class="clear"/>
			<li><label for="name"><?php echo $sLang["orders"]["main_your_comment"] ?></label><textarea name="comment" class="w200" rows="8" style="width:300px"><?php echo $orderMain["comment"]?></textarea></li><li class="clear"/>
			<li><label for="name"><?php echo $sLang["orders"]["main_customer_comment"] ?></label><textarea class="w200" name="customercomment" rows="8" style="width:300px"><?php echo  preg_replace("/[^a-z\d äÄüÜöÖß]/i", "",  $orderMain["customercomment"] );?></textarea></li><li class="clear"/>
			</ul>
						</ul>
			<div class="buttons" id="buttons">
				<ul>
				<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
				<button type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["orders"]["main_save"] ?></div></button>
				</li>	
				</ul>
			</div>
		</fieldset>
	</form>
</div>

</body>
</html>