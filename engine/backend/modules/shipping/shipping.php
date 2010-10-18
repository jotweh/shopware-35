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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" />
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="js/pricebox.js"></script>
<link href="js/groupmenu.css" rel="stylesheet" type="text/css">
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />

<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

<title><?php echo $sLang["shipping"]["shipping_forwarding_expenses"] ?></title>
<style>
	select {
		width:148px;
	}
	input[type="text"] {
		width:139px;
	}
	#pricetemplateEK input[type="text"] {
		width:50px;
	}
</style>
</head>
<script>
function deleteShippingConfirm(text,ev){
		parent.sConfirmationObj.show('<?php echo $sLang["shipping"]["shipping_should_the_dispatch"] ?> "'+text+'" <?php echo $sLang["shipping"]["shipping_really_be_deleted"] ?>',window,'deleteShipping',ev);
}
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteShipping":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?deleteShippingType="+sId;
			break;
		case "newSupplier":
		window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?new=1";
			break;
		case "saveSupplier":
		try {
			$('save').submit();
		}catch (e) {} 
		
		break;
	}
}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>
</script>
		
<body onload="generateInnerHtml();">
<?php
$sCore->sInitTranslations(1,"config_dispatch","true");
?>
<fieldset class="col2_cat2">
<legend><a class="ico help"></a><?php echo $sLang["shipping"]["shipping_forwarding_expenses"] ?></legend>
<strong><?php echo $sLang["shipping"]["shipping_to_define_supplements_for_payment_methods"] ?> <a href="#" onclick="parent.loadSkeleton('payment')"><?php echo $sLang["shipping"]["shipping_here"] ?></a></strong>
<br />
<strong><?php echo $sLang["shipping"]["shipping_please_make_sure"] ?></strong>
<br />
<strong>
<a href="#" onclick="parent.myExt.displayLicense();return false;">Jetzt auf Premium-Versandkostenmodul updaten</a>
</strong>
</fieldset>
<?php
/*
if ($_POST["sShippingFreeFrom"]){
	$updateShippingFree = mysql_query("UPDATE s_core_config SET value={$_POST["sShippingFreeFrom"]} WHERE name='sSHIPPINGFREEFROM'");
}
$getShippingFree = mysql_query("SELECT value FROM s_core_config WHERE name='sSHIPPINGFREEFROM'");
$_POST["sShippingFreeFrom"] = mysql_result($getShippingFree,0,"value");
*/
?>
<!--
<form id="ourForm" name="frmGlobal" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
<fieldset>
	<legend>Globale Einstellungen</legend>
	<ul>
	 <li><label style="width:150px; text-align:left" for="name">Versandkostenfrei ab:</label><input name="sShippingFreeFrom" type="text" id="email" style="height:20px;width:50px" class="w200" value="<?php echo $_POST["sShippingFreeFrom"] ?>" /></li>
	 <li class="clear"/>
	 <li><a class="ico add" style="cursor:pointer" onclick="$('ourForm').submit();"></a> Speichern</li>
	 
	
	 
	</ul>		
</fieldset>
</form>
-->

<?php
if(!empty($_REQUEST["typeID"]))
	$typeID = (int) $_REQUEST["typeID"];
	
if(!empty($_REQUEST["deleteShippingType"]))
{
	$type = (int) $_REQUEST["deleteShippingType"];
	$sql = "DELETE FROM s_shippingcosts WHERE typeID=$type";
	mysql_query($sql);
	$sql = "DELETE FROM s_shippingcosts_dispatch WHERE id=$type";
	mysql_query($sql);
	$sql = "DELETE FROM s_shippingcosts_dispatch_countries WHERE typeID=$type";
	mysql_query($sql);
}
// Insert new Dispatch
if(!empty($_REQUEST["type"]))
{
	$type = mysql_real_escape_string($_REQUEST["type"]);
	$description = mysql_real_escape_string($_POST["types_description"]);
	$position = intval($_POST["types_position"]);
	$typeShippingfree = intval($_POST["types_shippingfree"]);
	
	if (!$position) $position = "1";
	if (!$typeShippingfree) $typeShippingfree = "0";
	
	$sql = "INSERT INTO s_shippingcosts_dispatch (name,description,active,position,shippingfree) VALUES ('$type','', 1,$position,$typeShippingfree);";
	mysql_query($sql);
	$typeID = mysql_insert_id();
	echo "<script>parent.Growl('Versandart $description wurde angelegt');</script>";
	
}
// Update Description
if(empty($_REQUEST["type"]) &&  (!empty($_POST["types_description"]) || !empty($_POST["types_position"]))){
	$description = mysql_real_escape_string($_POST["types_description"]);
	$position = intval($_POST["types_position"]);
	$typeShippingfree = intval($_POST["types_shippingfree"]);
	$name =  mysql_real_escape_string($_POST["types_name"]);
	$sql = "
	UPDATE s_shippingcosts_dispatch SET description = '$description',
	name = '$name',
	position = '$position',
	shippingfree = '$typeShippingfree'
	WHERE id = {$_POST["typeID"]}
	"; 
	$updateDescription = mysql_query($sql);
	
	echo "<script>parent.Growl('Versandart $name wurde bearbeitet');</script>";
}

if(!empty($_REQUEST["types_countries"])&&is_array($_REQUEST["types_countries"]))
{
	$sql = "DELETE FROM s_shippingcosts_dispatch_countries WHERE typeID=$typeID";
	mysql_query($sql);
	$sql = array();
	foreach ($_REQUEST["types_countries"] as $country) {
		$country = (int) $country;
		$sql[] = "($typeID, $country)";
	}
	$sql = "INSERT INTO s_shippingcosts_dispatch_countries (typeID,countryID) VALUES ".implode(", ",$sql);
	mysql_query($sql);
}
$shippingcosts_dispatch = array();
$result = mysql_query("SELECT *  FROM s_shippingcosts_dispatch ORDER BY position");
if($result&&mysql_num_rows($result)) { 
	while ($row = mysql_fetch_assoc($result)) {
		$shippingcosts_dispatch[$row["id"]] = $row;
	}
}

if(empty($typeID))
	$typeID = key($shippingcosts_dispatch);
if(empty($typeID))
	$typeID = 1;

$typeName = $shippingcosts_dispatch[$typeID]["name"];
$typePosition = $shippingcosts_dispatch[$typeID]["position"]; // Position
$typeDescription = $shippingcosts_dispatch[$typeID]["description"];
$typeShippingfree = $shippingcosts_dispatch[$typeID]["shippingfree"];

$shippingcosts_countries = array();
$result = mysql_query("SELECT id , countryname as name, IF(tc.countryID,1,0) as active FROM s_core_countries c LEFT JOIN s_shippingcosts_dispatch_countries tc ON c.id=tc.countryID AND typeID=$typeID ORDER BY position");
if($result&&mysql_num_rows($result))
{ 
	while ($row = mysql_fetch_assoc($result)) {
		$shippingcosts_countries[$row["id"]] = $row;
	}
}
?>

<fieldset>
	<legend><?php echo $sLang["shipping"]["shipping_shipment_settings"] ?></legend>
	<ul>
	<form id="ourTypeSelect" name="fromTypeSelect" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	
	<li><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_dispatch_selection"]?></label>
	<select name="typeID"  onchange="window.location.href='<?php echo$_SERVER["PHP_SELF"]?>?typeID='+this.value;">
	<?php foreach ($shippingcosts_dispatch as $key=>$type){?>
	<option value="<?php echo$key?>" <?php if($typeID==$key) echo "selected"?>><?php echo $type["name"]?></option>
	<?php }?>
	</select>
	</li>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_or_new"] ?></label><input type="text" name="type" /></li>
	<li style="clear:both;border-bottom:1px solid #ddd;width:400px"></li>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_title"] ?></label>
	<input id="dispatch_name" type="text" name="types_name" value="<?php echo $typeName ?>">
	<?php
		echo $sCore->sBuildTranslation("dispatch_name","dispatch_name","1","config_dispatch",$typeID);
	?>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_position"] ?></label>
	<input type="text" name="types_position" value="<?php echo $typePosition ?>">
	</li>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_valid_for_free"] ?></label>
	<input type="checkbox" name="types_shippingfree" value="1" <?php echo $typeShippingfree ? "checked" : ""?>>
	</li>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_description"] ?></label>
	<textarea id="dispatch_description" name="types_description"><?php echo $typeDescription ?></textarea>
	<?php
		echo $sCore->sBuildTranslation("dispatch_description","dispatch_description","1","config_dispatch",$typeID);
	?>
	</li>
	<li style="clear:both;"><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_country_selection"] ?></label>
	<select name="types_countries[]" multiple="multiple" size="6" onchange="">
	<?php foreach ($shippingcosts_countries as $countryID=>$country){?>
	<option value="<?php echo$countryID?>" <?php if($country["active"]) echo "selected"?>><?php echo$country["name"]?></option>
	<?php }?>
	</select>
	</li>
	<li class="clear"></li>
	<li><br/></li>
		<li class="clear"></li>
	
	 <div class="buttons" id="buttons">
		<ul>	
		
			<li id="buttonTemplate" class="buttonTemplate"><button onclick="$('ourTypeSelect').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["shipping"]["shipping_save"] ?></div></button></li>	
			
	
			<li id="buttonTemplate" class="buttonTemplate"><a class="bt_icon delete"  onclick="deleteShippingConfirm('<?php echo $typeName ?>',<?php echo $typeID?>)"><?php echo $sLang["shipping"]["shipping_delete_dispatch"] ?></a></li>
		
	 </ul>
		</div>

	

	
	</form>
	</ul>
</fieldset>

<?php
if ($_POST["sSaveGlobal"]){
	if ($_POST["area"]){
		// Delete previous rows
		$deleteAreas = mysql_query("
		DELETE FROM s_shippingcosts WHERE area='{$_POST["area"]}' AND typeID=$typeID
		");
		
		foreach ($_POST["von"]["EK"] as $key => $from){
			if($_POST["bis"]["EK"][$key]=="beliebig")
				$to = 0;
			else 
				$to  = $_POST["bis"]["EK"][$key];
				
				
			$costs = $_POST["priceregulary"]["EK"][$key] ? $_POST["priceregulary"]["EK"][$key] : "0";
			$costs = str_replace(",",".",$costs);
			$factor = $_POST["pricefactor"]["EK"][$key] ? $_POST["pricefactor"]["EK"][$key] : "0";
			$factor = str_replace(",",".",$factor);
			$sql = "
				INSERT INTO s_shippingcosts (`from`,`to`,shippingcosts,factor, area, countryID, typeID)
				VALUES ('$from','$to',$costs,$factor,'{$_POST["area"]}',0,$typeID)
			";
			//echo $sql;
			$insertValue = mysql_query($sql);
			if(empty($to))
				break;
		}
		echo "<script>parent.Growl('Zonen-Versandkosten ({$_POST["area"]}) wurden gespeichert');</script>";
	}
}
if (!$_POST["area"]) $_POST["area"] = "deutschland";

// Load data
$sql = "
SELECT `from` AS start, `to` AS end, shippingcosts, factor FROM s_shippingcosts WHERE area='{$_POST["area"]}' AND typeID=$typeID ORDER BY id ASC
";
$queryShippingCosts = mysql_query($sql);


?>
<script language="javascript">
		pricegroups=new Array();
		pricegroups[0]="EK";
		pricegroups[1]="COUNTRY";
		
		var staffel = new Array();		
    		staffel["EK"] = new Array();
    		staffel["COUNTRY"] = new Array();
    		
    		<?php
    			$i=0;
    		if (@mysql_num_rows($queryShippingCosts)){
    			while ($cost = mysql_fetch_array($queryShippingCosts)){
    				if(empty($cost["end"]))
    					$cost["end"] = "\"beliebig\"";
    				echo "
    				staffel[\"EK\"][$i] = new Array();
		    		staffel[\"EK\"][$i][\"von\"] = {$cost["start"]};
		    		staffel[\"EK\"][$i][\"bis\"] = {$cost["end"]};
		    		staffel[\"EK\"][$i][\"pricevk\"] = \"{$cost["shippingcosts"]}\";
		    		staffel[\"EK\"][$i][\"pricefactor\"] = \"{$cost["factor"]}\";
		    		staffel[\"EK\"][$i][\"pricepseudo\"] = \"0\";
		    		staffel[\"EK\"][$i][\"priceek\"] = \"0\";
    				";
    				$i++;
    			}
    		}else {
    			echo "
				staffel[\"EK\"][0] = new Array();
	    		staffel[\"EK\"][0][\"von\"] = \"0\";
	    		staffel[\"EK\"][0][\"bis\"] = \"beliebig\";
	    		staffel[\"EK\"][0][\"pricevk\"] = \"0\";
	    		staffel[\"EK\"][0][\"pricefactor\"] = \"0\";
	    		staffel[\"EK\"][0][\"pricepseudo\"] = \"0\";
	    		staffel[\"EK\"][0][\"priceek\"] = \"0\";
				";
    		}
    		?>
		defaultmwst=new Array();defaultmwst[0]="1";
</script>

<fieldset>
	<legend><?php echo $sLang["shipping"]["shipping_zone_settings"] ?></legend>
	<ul>
	<form id="ourFormSelect" name="frmGlobal2" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	<input type="hidden" value="<?php echo$typeID?>" name="typeID" />
	
	<li><label style="width:150px; text-align:left" for=""><?php echo $sLang["shipping"]["shipping_dispatch"] ?></label><?php echo$typeName?></li>
	<li class="clear"/>
	<li><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_selection_group"] ?></label>
	<select name="area" onchange="$('ourFormSelect').submit()">
	<?php
		// Read groups
		$getGroups = mysql_query("
		SELECT * FROM s_shippingcosts_areas WHERE active=1 ORDER BY position ASC
		");
		while ($sGroup = mysql_fetch_array($getGroups)){
	?>
	<option value="<?php echo $sGroup["name"] ?>" <?php echo $_POST["area"]==$sGroup["name"] ? "selected" : ""?>><?php echo $sGroup["description"]?></option>
	<?php
		}
	?>
	
	
	</select>
	</li>
	<?php
	if ($_POST["area"]){
		if ($_POST["sShippingfreeArea"]){
			//die("TEST");
			$_POST["sShippingfreeArea"] = str_replace(",",".",$_POST["sShippingfreeArea"]);
			$updateShippingFree = mysql_query("
			UPDATE s_shippingcosts_areas SET shippingfree = {$_POST["sShippingfreeArea"]}
			WHERE name = '{$_POST["area"]}'
			");
		}
		$getShippingFree = mysql_query("
		SELECT shippingfree FROM s_shippingcosts_areas WHERE name = '{$_POST["area"]}'
		");
		$shippingfree = @mysql_result($getShippingFree,0,"shippingfree");
	}
	?>
	
	</form>
	<form id="ourForm2" name="frmGlobal2" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	
	<input type="hidden" name="typeID" value="<?php echo$typeID?>">
	<input type="hidden" name="sSaveGlobal" value=1>
	<input type="hidden" name="area" value="<?php echo $_POST["area"] ?>">
	<li><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_free_shipping_from"] ?></label>
	<input type="text" value="<?php echo $shippingfree ?>" name="sShippingfreeArea">
	</li>
	<div class="clear"></div>
  		<li><fieldset class="grey" style="margin:0;padding:0 0 0 0;"><div id="pricetemplateEK">
  		
  		</div></fieldset></li>
  		
	<li class="clear"></li>

		
	 <div class="buttons" id="buttons">
		<ul>	
			<li id="buttonTemplate" class="buttonTemplate"><button onclick="$('ourForm2').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["shipping"]["shipping_save"] ?></div></button></li>	
	 </ul>
		</div>
	
	
	</form>
	</ul>
</fieldset>


<?php

if ($_GET["deleteCountryRelations"]){
	$deleteAreas = mysql_query("
	DELETE FROM s_shippingcosts WHERE countryID='{$_GET["deleteCountryRelations"]}' AND typeID=$typeID
	");
}

if ($_POST["sSaveGlobalCountry"]){
	if ($_POST["country"]){
		// Delete previous rows
		$deleteAreas = mysql_query("DELETE FROM s_shippingcosts WHERE countryID={$_POST["country"]} AND typeID=$typeID");
		#echo "DELETE FROM s_shippingcosts WHERE countryID={$_POST["country"]} AND typeID=$typeID";
		foreach ($_POST["von"]["COUNTRY"] as $key => $from){
			$to  = $_POST["bis"]["COUNTRY"][$key];
			if(!empty($to)&&$to=="beliebig")
				$to = 0;
			$costs = $_POST["priceregulary"]["COUNTRY"][$key] ? $_POST["priceregulary"]["COUNTRY"][$key] : "0";
			$costs = str_replace(",",".",$costs);
			
			$factor = $_POST["pricefactor"]["COUNTRY"][$key] ? $_POST["pricefactor"]["COUNTRY"][$key] : "0";
			$factor = str_replace(",",".",$factor);
			if (empty($costs)) continue;
			$sql = "
			INSERT INTO s_shippingcosts (`from`,`to`,shippingcosts,factor, area, countryID, typeID)
			VALUES ('$from','$to',$costs,$factor,'',{$_POST["country"]},$typeID)
			";
			$insertValue = mysql_query($sql);
			echo "<script>parent.Growl('Länder-Versandkosten ({$_POST["country"]}) wurden gespeichert');</script>";
			if(empty($to))
				break;
		}
		
	}
}

if (isset($_POST["country"])){
	
		$sql = "SELECT `from` AS start, `to` AS end, shippingcosts, factor FROM s_shippingcosts WHERE countryID='{$_POST["country"]}' AND typeID=$typeID ORDER BY start ASC";
		$queryShippingCosts = mysql_query($sql);
		

		if (empty($_POST["sShippingfreeCountry"])) $_POST["sShippingfreeCountry"] = "0.00";
		
		if ($_POST["sShippingfreeCountry"] && !empty($_POST["sSaveGlobalCountry"])){
			
			$_POST["sShippingfreeCountry"] = str_replace(",",".",$_POST["sShippingfreeCountry"]);
			$sql = "
			UPDATE s_core_countries SET shippingfree = {$_POST["sShippingfreeCountry"]}
			WHERE id = '{$_POST["country"]}'
			";
			
			$updateShippingFree = mysql_query($sql);
			
		}
		
		$getShippingFree = mysql_query("
		SELECT shippingfree FROM s_core_countries WHERE id = '{$_POST["country"]}'
		");
		$sShippingfreeCountry = @mysql_result($getShippingFree,0,"shippingfree");
}

?>

<script language="javascript">
    		<?php
    			$i=0;
    		if (@mysql_num_rows($queryShippingCosts)){
    			while ($cost = mysql_fetch_array($queryShippingCosts)){
    				if(empty($cost["end"])) $cost["end"] = "beliebig";
    				echo "//".$cost["end"];
    				echo "
    				staffel[\"COUNTRY\"][$i] = new Array();
		    		staffel[\"COUNTRY\"][$i][\"von\"] = \"{$cost["start"]}\";
		    		staffel[\"COUNTRY\"][$i][\"bis\"] = \"{$cost["end"]}\";
		    		staffel[\"COUNTRY\"][$i][\"pricevk\"] = \"{$cost["shippingcosts"]}\";
		    		staffel[\"COUNTRY\"][$i][\"pricefactor\"] = \"{$cost["factor"]}\";
		    		staffel[\"COUNTRY\"][$i][\"pricepseudo\"] = \"0\";
		    		staffel[\"COUNTRY\"][$i][\"priceek\"] = \"0\";
    				";
    				$i++;
    			}
    		}else {
    			echo "
				staffel[\"COUNTRY\"][0] = new Array();
	    		staffel[\"COUNTRY\"][0][\"von\"] = \"0\";
	    		staffel[\"COUNTRY\"][0][\"bis\"] = \"beliebig\";
	    		staffel[\"COUNTRY\"][0][\"pricevk\"] = \"0\";
	    		staffel[\"COUNTRY\"][0][\"pricefactor\"] = \"0\";
	    		staffel[\"COUNTRY\"][0][\"pricepseudo\"] = \"0\";
	    		staffel[\"COUNTRY\"][0][\"priceek\"] = \"0\";
				";
    		}
    		?>
		defaultmwst=new Array();defaultmwst[0]="1";
</script>



<fieldset>
	<legend><?php echo $sLang["shipping"]["shipping_optional"] ?></legend>
	<ul>
	<form id="ourFormSelect2" name="frmGlobal2" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	<input type="hidden" name="typeID" value="<?php echo$typeID?>">
	<li><label style="width:150px; text-align:left" for=""><?php echo $sLang["shipping"]["shipping_dispatch"] ?></label><?php echo$typeName?></li>
	<li class="clear"/>
	<li><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_locations"] ?></label>
	<select name="country" onchange="$('ourFormSelect2').submit()">
	<option value=""><?php echo $sLang["shipping"]["shipping_please_select"] ?></option>
	<?php
		$sql = "
			SELECT DISTINCT
				c.id as countryID,
				countryname as name,
				IF(s.id,1,0) as selected
			FROM 
				s_core_countries c
			LEFT JOIN s_shippingcosts s 
			ON s.countryID=c.id 
			AND s.typeID=$typeID 
			AND s.`from`=0
			LEFT JOIN s_shippingcosts_dispatch_countries tc
			ON tc.typeID=$typeID
			AND c.id=tc.countryID
			WHERE tc.typeID
			ORDER BY position ASC
		";
		$result = mysql_query($sql);
		if($result&&mysql_num_rows($result))
		{
			while ($country=mysql_fetch_array($result)){
					?>
					<option <?php if($country["selected"]) echo "style=\"background-color:#F00\"";?> value="<?php echo$country["countryID"]?>" <?php echo$_POST["country"]==$country["countryID"] ? "selected" : ""?>><?php echo$country["name"]?></option>
					<?php
			}
		}
	?>
	</select>
	</li>
	</form>
	<?php
		if ($_POST["country"]){
	?>
	<form id="ourForm3" name="frmGlobal3" method="POST" action="<?php echo $_SERVER["PHP_SELF"]?>">
	<input type="hidden" name="typeID" value="<?php echo$typeID?>">
	<input type="hidden" name="sSaveGlobalCountry" value="1">
	<input type="hidden" name="country" value="<?php echo $_POST["country"] ?>">
	<li><label style="width:150px; text-align:left" for="name"><?php echo $sLang["shipping"]["shipping_free_shipping_from"] ?></label>
	<input type="text" value="<?php echo $sShippingfreeCountry ?>" name="sShippingfreeCountry">
	</li>
	<div class="clear"></div>
  		<li><fieldset class="grey" style="margin:0;padding:0 0 0 0;"><div id="pricetemplateCOUNTRY">
  		</div></fieldset></li>
	<li class="clear"/>
	
		<li class="clear"></li>
	<li><br/></li>
		<li class="clear"></li>
	
	 <div class="buttons" id="buttons">
		<ul>	
		
			<li id="buttonTemplate" class="buttonTemplate"><button onclick="$('ourForm3').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["shipping"]["shipping_save"] ?></div></button></li>	
			
	
			<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"]."?deleteCountryRelations={$_POST["country"]}&typeID=$typeID"?>" class="bt_icon delete" style="text-decoration:none;"><?php echo $sLang["shipping"]["shipping_delete_assignment"] ?></a></li>
		
	 </ul>
		</div>
	
	
	</form>
	<?php
		}
	?>
	</ul>
</fieldset>
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>

