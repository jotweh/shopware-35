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

// Query all customer-groups
$getCustomerGroups = mysql_query("
SELECT groupkey, description FROM s_core_customergroups ORDER BY id ASC
");

// Query all customer-groups
$getShops = mysql_query("
SELECT id, name FROM `s_core_multilanguage`
");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script>
function cAlphanumeric(length){
	pool = new Array ("0","1","2","3","4","5","6","7","8","9",
						"a","b","c","d","e","f","g","h","i","j","k","l","m",
						"n","o","p","q","r","s","t","u","v","w","x","y","z");
	i = 0;
	an = "";
	while (i < length)
	{
		i = i + 1;
		ze = Math.floor(36 * Math.random());
		ze = pool [ze];
		an = an + ze;
	}
	return an;

}
</script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<body>

<style type="text/css">
fieldset {
	position:relative; 
	margin: 5px 0px 0px;
	padding-top: 15px;
}
label{
	text-align:left; 
	width:110px; 
	float:left;
}
label.forRadio{
	width:170px; 
}
.div_clear{
	clear:both; 
	height:3px;
}
hr {
	border: 0px;
	border-top: solid 1px #A9A9A9;
	border-bottom: transparent;

}
</style>



<fieldset style="margin-top:-20px; margin-bottom:20px; padding-top:0px; display:block;" class="fieldset_menu">
	<legend>Weitere Optionen</legend>
	<div class="buttons" id="buttons">
		<ul>			
		<li style="margin-top:10px; margin-right:3px;" id="buttonTemplate" class="buttonTemplate"><a class="bt_icon user_add" onClick="addNewUser();"  value="send"><?php echo $sLang["useradd"]["btn_menu_add_addition_user"]; ?></a></li>	
		<li style="margin-top:10px; margin-right:3px;" id="buttonTemplate" class="buttonTemplate"><a class="bt_icon pencil_arrow" onClick="openUserdetails();"  value="send"><?php echo $sLang["useradd"]["btn_menu_open_useraccount"]; ?></a></li>	
		<li style="margin-top:10px; margin-right:3px;" id="buttonTemplate" class="buttonTemplate"><a class="bt_icon sticky_notes_pin" onClick="doOrder();"  value="send"><?php echo $sLang["useradd"]["btn_menu_order_with_user"]; ?></a></li>	
		</ul>
	</div>
</fieldset>
<input id="saved_user"type="hidden">

<script type="text/javascript">
function doOrder(){
	
	var user_id = $('saved_user').value;
	Ext.Ajax.request({
	   url: '../../../backend/ajax/getUserShop.php',
	   params: 	{id: user_id},
	   success: function(response, result){
	   		var domain = response.responseText;
	   		
	   		new Request({url: '../../../../backend/UserLogin?id='+user_id, 
			onFailure: function (el){
				parent.parent.Growl("Benutzer "+user_id+" konnte nicht initialisiert werden}");
			},
			onComplete: function (response){
				
				if (response!="FAIL"){
			
					myWindow = window.open(domain+"?sCoreId="+response,"Login");
					myWindow.focus();
				}else {
					parent.parent.Growl(response);
				}
			}
			}).get();	
	   }
	});
			
		
}
</script>



<fieldset style="margin-top:-20px;" class="fieldset_add">
	<legend><?php echo $sLang["useradd"]["skeleton_KeyData"] ?></legend>
	
	<label for="sBillingEmail"><?php echo $sLang["useradd"]["mail_address"]; ?>*</label>
	<input name="sBillingEmail" type="text" id="sBillingEmail" class="w200" value="<?php echo $sValues["sBillingEmail"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingPassword"><?php echo $sLang["useradd"]["password"]; ?>*</label>
	<input name="sBillingPassword" type="text" id="sBillingPassword" class="w200" value="<?php echo $sValues["sBillingPassword"] ?>" />
	<a style="cursor:pointer; position:relative; top:3px;" class="ico key_plus" 
		title="Automatisch generieren" onclick="cPw();"></a>
	
	<div class="div_clear"></div>
	
	<label for="sCustomerGroup"><?php echo $sLang["useradd"]["list_customergroup"]; ?></label>
	<select name="sCustomerGroup" id="sCustomerGroup" class="w200">
		<?php
		while ($customerGroup=mysql_fetch_array($getCustomerGroups)){
		?>
			<option <?php echo $customerGroup["groupkey"]==$sValues["customergroup"] ? "selected" : "" ?> value="<?php echo $customerGroup["groupkey"]?>"><?php echo $customerGroup["description"] ?></option>
		<?php
		}
		?>
	</select>
	
	<div class="div_clear"></div>
	
	<label for="sMultiShop"><?php echo $sLang["useradd"]["list_shop"]; ?></label>
	<select name="sMultiShop" id="sMultiShop" class="w200">
		<?php
		while ($shop=mysql_fetch_array($getShops)){
		?>
			<option <?php echo $shop["id"]==$sValues["sMultiShop"] ? "selected" : "" ?> value="<?php echo $shop["id"]?>"><?php echo $shop["name"] ?></option>
		<?php
		}
		?>
	</select>
</fieldset>

<fieldset class="fieldset_add">
	<legend><?php echo $sLang["useradd"]["billing_address"]; ?></legend>
	
	<label for="sBillingTitle"><?php echo $sLang["useradd"]["title"]; ?></label>
	<select name="sBillingTitle" id="sBillingTitle" class="w200">
		<option value="mr" <?php echo $sValues["salutation"]=="mr" ? "selected" : ""?>><?php echo $sLang["useradd"]["mister"]; ?></option>
		<option value="ms" <?php echo $sValues["salutation"]=="ms" ? "selected" : ""?>><?php echo $sLang["useradd"]["miss"]; ?></option>
		<option value="company" <?php echo $sValues["salutation"]=="company" ? "selected" : ""?>><?php echo $sLang["useradd"]["company"]; ?></option>
	</select>
	
	<div class="div_clear"></div>
	
	<label for="sBillingCompany"><?php echo $sLang["useradd"]["company"]; ?>:</label>
	<input name="sBillingCompany" type="text" id="sBillingCompany" class="w200" value="<?php echo $sValues["sBillingCompany"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingDepartment"><?php echo $sLang["useradd"]["department"]; ?></label>
	<input name="sBillingDepartment" type="text" id="sBillingDepartment" class="w200" value="<?php echo $sValues["sBillingDepartment"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingFirstname"><?php echo $sLang["useradd"]["firstname"]; ?>*</label>
	<input name="sBillingFirstname" type="text" id="sBillingFirstname" class="w200" value="<?php echo $sValues["sBillingFirstname"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingLastname"><?php echo $sLang["useradd"]["lastname"]; ?>*</label>
	<input name="sBillingLastname" type="text" id="sBillingLastname" class="w200" value="<?php echo $sValues["sBillingLastname"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingStreet"><?php echo $sLang["useradd"]["street"]; ?>*</label>
	<input name="sBillingStreet" type="text" id="sBillingStreet" class="w200" value="<?php echo $sValues["sBillingStreet"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingHouseN"><?php echo $sLang["useradd"]["house_no"]; ?>*</label>
	<input name="sBillingHouseN" type="text" id="sBillingHouseN" class="w200" value="<?php echo $sValues["sBillingHouseN"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingZipcode"><?php echo $sLang["useradd"]["postal_Code"]; ?>*</label>
	<input name="sBillingZipcode" type="text" id="sBillingZipcode" class="w200" value="<?php echo $sValues["sBillingZipcode"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingCity"><?php echo $sLang["useradd"]["city"]; ?>*</label>
	<input name="sBillingCity" type="text" id="sBillingCity" class="w200" value="<?php echo $sValues["sBillingCity"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingPhone"><?php echo $sLang["useradd"]["phone"]; ?></label>
	<input name="sBillingPhone" type="text" id="sBillingPhone" class="w200" value="<?php echo $sValues["sBillingPhone"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingFax"><?php echo $sLang["useradd"]["fax"]; ?></label>
	<input name="sBillingFax" type="text" id="sBillingFax" class="w200" value="<?php echo $sValues["sBillingFax"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingTax"><?php echo $sLang["useradd"]["tax"]; ?></label>
	<input name="sBillingTax" type="text" id="sBillingTax" class="w200" value="<?php echo $sValues["sBillingTax"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sBillingCountry"><?php echo $sLang["useradd"]["country"]; ?>*</label>
	<select name="sBillingCountry" id="sBillingCountry" class="w200">
		<?php
		$query = mysql_query("SELECT countryname, id FROM s_core_countries ORDER BY position");
		while ($country=mysql_fetch_array($query)){
			if ($country["id"]==$sValues["sBillingCountry"]) $sel = "selected"; else $sel = "";
			echo "<option value=\"{$country["id"]}\" $sel>".$country["countryname"]."</option>";
		}
		?>
	</select>
	
</fieldset>
<fieldset class="fieldset_add">
	<legend><?php echo $sLang["useradd"]["delivery_address"]; ?></legend>
		
	<a style="cursor:pointer; float:left; position:relative; right:5px;" class="ico pencil_plus" 
		title="automatisch generieren" onclick="applyData();"></a>
	<label style="width:250px;" onclick="applyData();"><strong><?php echo $sLang["useradd"]["magic_key_autocomplete"]; ?></strong></label>
	
	<div class="div_clear"></div>
	<hr>
	<div class="div_clear"></div>
	
	<label for="sDeliveryTitle"><?php echo $sLang["useradd"]["title"]; ?></label>
	<select name="sDeliveryTitle" id="sDeliveryTitle" class="w200">
		<option value="mr" <?php echo $sValues["salutation"]=="mr" ? "selected" : ""?>><?php echo $sLang["useradd"]["mister"]; ?></option>
		<option value="ms" <?php echo $sValues["salutation"]=="ms" ? "selected" : ""?>><?php echo $sLang["useradd"]["miss"]; ?></option>
		<option value="company" <?php echo $sValues["salutation"]=="company" ? "selected" : ""?>><?php echo $sLang["useradd"]["company"]; ?></option>
	</select>
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryCompany"><?php echo $sLang["useradd"]["company"]; ?>:</label>
	<input name="sDeliveryCompany" type="text" id="sDeliveryCompany" class="w200" value="<?php echo $sValues["sDeliveryCompany"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryDepartment"><?php echo $sLang["useradd"]["department"]; ?></label>
	<input name="sDeliveryDepartment" type="text" id="sDeliveryDepartment" class="w200" value="<?php echo $sValues["sDeliveryDepartment"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryFirstname"><?php echo $sLang["useradd"]["firstname"]; ?>*</label>
	<input name="sDeliveryFirstname" type="text" id="sDeliveryFirstname" class="w200" value="<?php echo $sValues["sDeliveryFirstname"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryLastname"><?php echo $sLang["useradd"]["lastname"]; ?>*</label>
	<input name="sDeliveryLastname" type="text" id="sDeliveryLastname" class="w200" value="<?php echo $sValues["sDeliveryLastname"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryStreet"><?php echo $sLang["useradd"]["street"]; ?>*</label>
	<input name="sDeliveryStreet" type="text" id="sDeliveryStreet" class="w200" value="<?php echo $sValues["sDeliveryStreet"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryHouseN"><?php echo $sLang["useradd"]["house_no"]; ?>*</label>
	<input name="sDeliveryHouseN" type="text" id="sDeliveryHouseN" class="w200" value="<?php echo $sValues["sDeliveryHouseN"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryZipcode"><?php echo $sLang["useradd"]["postal_Code"]; ?>*</label>
	<input name="sDeliveryZipcode" type="text" id="sDeliveryZipcode" class="w200" value="<?php echo $sValues["sDeliveryZipcode"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryCity"><?php echo $sLang["useradd"]["city"]; ?>*</label>
	<input name="sDeliveryCity" type="text" id="sDeliveryCity" class="w200" value="<?php echo $sValues["sDeliveryCity"] ?>" />
	
	<div class="div_clear"></div>
	
	<label for="sDeliveryCountry"><?php echo $sLang["useradd"]["country"]; ?>*</label>
	<select name="sDeliveryCountry" id="sDeliveryCountry" class="w200">
		<?php
		$query = mysql_query("SELECT countryname, id FROM s_core_countries ORDER BY position");
		while ($country=mysql_fetch_array($query)){
			if ($country["id"]==$sValues["sDeliveryCountry"]) $sel = "selected"; else $sel = "";
			echo "<option value=\"{$country["id"]}\" $sel>".$country["countryname"]."</option>";
		}
		?>
	</select>
	
</fieldset>

<fieldset class="fieldset_add">
	<legend><?php echo $sLang["useradd"]["kind_of_payment"]; ?></legend>
	<?php
		// Alle Zahlungsarten auslesen ...
		$getPaymentMeans = mysql_query("
		SELECT * FROM
		s_core_paymentmeans ORDER BY name ASC
		");
		
		
		while ($paymentMean = mysql_fetch_array($getPaymentMeans)){
			//active and inactive highlighting
			if($paymentMean['active'] == 0)
			{
				$payment_color = "red";
			}else{
				$payment_color = "green";
			}
			
			
			if ($paymentMean["table"]){
				$table = $paymentMean["table"];
				$showColumns = mysql_query("
				SHOW COLUMNS FROM $table
				");
				while ($field = mysql_fetch_assoc($showColumns)){
					$fields[] = $field["Field"];
				}
			}else {
				unset($fields);
			}?>
			
			<label class="forRadio"><font color="<?php echo $payment_color ?>"><?php echo $paymentMean['description'] ?>:</font></label>
			<input id='sPayment' name="sPaymentType" type="radio" value="<?php echo $paymentMean['id'] ?>"/>
			<div class="div_clear"></div>
			<?php 
			if($fields)
			{
				foreach($fields as $field)
				{
					if($field != "id" && $field != "userID")
					{
						//labelvalue
						if($sLang["useradd"][$field] != "")
						{
							$field_value = $sLang["useradd"][$field];
						}else{
							$field_value = "<font color='red'>no translation in language_xy.php [{$field}]</font>";
						}
						
						echo "<label id='lbl_".$field."' style='padding-left:30px; width:140px;'>$field_value</label>";
						echo "<input id='rbt_".$field."' name='$field' type='text' parentOf='".$paymentMean['id']."' tbl='".$paymentMean['table']."'/>";
						echo "<div class='div_clear'></div>";
					}
				}
			}
		}
	?>
</fieldset>
<div class="buttons" onclick="sSave();"  id="sSave2" style="margin-top:20px;display:block" id="buttons"><ul><li class="buttonTemplate">
<button  id="sSave" name="action" type="submit" value="save" class="button"><div class="buttonLabel">Kunde anlegen</div></button>
</div>

</body>
</html>

<script type="text/javascript">
function sSave()
{
	if(!validateForm())
	{
		sendRequest();
	}else{
	}
}
function cPw(){
	$('sBillingPassword').value = cAlphanumeric(8);
}
function applyData()
{
	items = ['Title',
			'Company',
			'Department',
			'Firstname',
			'Lastname',
			'Street',
			'HouseN',
			'Zipcode',
			'City',
			'Country'
			];
	for(var i=0; i<items.length; i++)
	{
		$('sDelivery'+items[i]).value = $('sBilling'+items[i]).value;
	}	
}
function validateForm(){
	var valErr = false;
	var firstEl = "";
	var mailtext = "";
	
	var neededBilFields = 
		['Password',
		'Firstname',
		'Lastname',
		'Street',
		'HouseN',
		'Zipcode',
		'City'
		];
		
	var neededDelFields = 
		['Firstname',
		'Lastname',
		'Street',
		'HouseN',
		'Zipcode',
		'City'
		];

	var eMailValue = $('sBillingEmail').value;
	if(eMailValue.trim() == "")
	{
		valErr = true;
		firstEl == "" ? firstEl = 'sBillingEmail' : firstEl = firstEl;
		$('sBillingEmail').style.border = "1px solid red";
	}
else if(!eMailValue.match("^.+@.+\\..+$")){
		valErr = true;
		mailtext = "Die eMail-Adresse ist ungültig!<br>";
		firstEl == "" ? firstEl = 'sBillingEmail' : firstEl = firstEl;
		$('sBillingEmail').style.border = "1px solid red";
	}else{
		//Reset
		$('sBillingEmail').style.border = "1px solid #BABFCD";
	}
		
	for(var i=0; i<neededBilFields.length; i++)
	{
		//Reset Border
		$('sBilling'+neededBilFields[i]).style.border = "1px solid #BABFCD";
		
		if($('sBilling'+neededBilFields[i]).value.trim() == "")
		{
			valErr = true;
			firstEl == "" ? firstEl = 'sBilling'+neededBilFields[i] : firstEl = firstEl;
			$('sBilling'+neededBilFields[i]).style.border = "1px solid red";
		}
	}
		
	for(var i=0; i<neededDelFields.length; i++)
	{
		//Reset Border
		$('sDelivery'+neededDelFields[i]).style.border = "1px solid #BABFCD";
		
		if($('sDelivery'+neededDelFields[i]).value.trim() == "")
		{
			valErr = true;
			firstEl == "" ? firstEl = 'sBilling'+neededBilFields[i] : firstEl = firstEl;
			$('sDelivery'+neededDelFields[i]).style.border = "1px solid red";
		}
	}
	
	var chb_checked;
	var selected_box_id=0;
	$$('label.forRadio').setStyle('color', 'inherit');
	$(document.body).getElements('input[name=sPaymentType]').each(function(item, index, allItems){
		if(item.checked == true)
		{
			chb_checked = true;
			selected_box_id = item.value;
		}
	});
	
	$(document.body).getElements('input[tbl]').each(function(item, index, allItems){
		var id = 'lbl_'+item.name;
		$(id).setStyle('color', 'inherit');
		
		if(selected_box_id!=0)
		{
			if(selected_box_id == item.getProperty('parentOf') && item.value == "")
			{
				$(id).setStyle('color', 'red');
				valErr = true;
				firstEl == "" ? firstEl = item.id : firstEl = firstEl;
			}
		}
	});

	if(chb_checked != true)
	{
		valErr = true;
		firstEl == "" ? firstEl = 'sPayment' : firstEl = firstEl;
		$$('label.forRadio').setStyle('color', 'red');
	}
	
	if(valErr)
	{
		parent.Growl(mailtext+"Bitte füllen Sie alle Pflichtfelder aus!");
		Ext.get(firstEl).focus();
	}
	return valErr;
}
function sendRequest()
{
	var items_req = "";
	var items =
	['sBillingEmail',
	'sBillingPassword',
	'sCustomerGroup',
	'sMultiShop',
	
	'sBillingTitle',
	'sBillingCompany',
	'sBillingDepartment',
	'sBillingFirstname',
	'sBillingLastname',
	'sBillingStreet',
	'sBillingHouseN',
	'sBillingZipcode',
	'sBillingCity',
	'sBillingPhone',
	'sBillingFax',
	'sBillingTax',
	'sBillingCountry',
//	
	'sDeliveryTitle',
	'sDeliveryCompany',
	'sDeliveryDepartment',
	'sDeliveryFirstname',
	'sDeliveryLastname',
	'sDeliveryStreet',
	'sDeliveryHouseN',
	'sDeliveryZipcode',
	'sDeliveryCity',
	'sDeliveryCountry'
	];
	
	var forms = new Object();
	for(var i=0; i<items.length; i++)
	{
//		if(items_req != "") items_req += ",";
//		items_req = items_req+items[i]+":"+$(items[i]).value;
		var key = items_req+items[i];
		var val = $(items[i]).value;
		forms[key] = val;
	}
	json_forms = Ext.encode(forms); 
	
	var paymentType;
	$(document.body).getElements('input[name=sPaymentType]').each(function(item, index, allItems){
		if(item.checked == true)
		{
			paymentType = item.value;
		}
	});
	
	var tbls = new Object();
	$(document.body).getElements('input[tbl]').each(function(item, index, allItems){
		var tbl = item.getProperty('tbl');
		var field = item.name;
		if(tbls[tbl] == null) tbls[tbl] = new Object();
		tbls[tbl][field] = new Object();
		tbls[tbl][field]['value'] = item.value;
	});
	var json_tbls = Ext.encode(tbls);
	
	Ext.Ajax.request({
	   url: 'saveUser.php',
		   success: function(response, options){
		   		if(response.responseText == "email_exist")
		   		{
		   			$('sBillingEmail').style.border = "1px solid red";
		   			parent.Growl("Die eMail-Adresse existiert bereits!");
					Ext.get('sBillingEmail').focus();
		   		}else{
		   			$('saved_user').value = response.responseText;
		   			$$('fieldset.fieldset_add').setStyle('display', 'none');
		   			$$('fieldset.fieldset_menu').setStyle('display', 'block');
		   			$('sSave2').setStyle('display', 'none');
		   		}
		   },
//		   failure: otherFn,
	   params: { forms: json_forms, payment: paymentType, tbls: json_tbls}
	});
}
function openUserdetails(){
	var user_id = $('saved_user').value;
	parent.loadSkeleton('userdetails', false, {user:user_id});
}
function addNewUser(){
	$$('fieldset.fieldset_add').setStyle('display', 'block');
	$$('fieldset.fieldset_menu').setStyle('display', 'none');
	$('sSave2').setStyle('display', 'block');
	
	//Reset Fields
	$(document.body).getElements('input', 'select').each(function(item, index, allItems){
		item.value = "";
	});
}
</script>