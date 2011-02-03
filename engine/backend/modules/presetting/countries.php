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

$valueName = $sLang["presettings"]["countries_country"];
$valueDelete = true;
$valueTable = "s_core_countries";
$valueAdd = true;
$valueDescription = "countryname";

/*
	Project: distributorChanges
	Package: disclaimer
	Date: 2010-08-26
	Author: Marcel Schmäing <ms@shopware.ag>
	Trac: #3618
	Description:Siehe Trac
	Felderhinzufügen
*/
$substitute =array(
"id"=>"hide",
"countryname"=>"Name des Landes",
"countryiso"=>"ISO-Code (2-stellig)",
"countryarea"=>"Lieferzonen Angabe (deutschland, europa, welt)",
"countryen"=>"Englische Bezeichnung des Landes",
"position"=>"Position des Landes in der Auswahlbox",
"notice"=>"Beschreibungstext (evtl. Zölle etc.)",
"shippingfree"=>"hide",
"active"=>"Aktiv",
"taxfree"=>"Immer Netto beliefern",
"taxfree_ustid"=>"Netto beliefern wenn UST-ID eingegeben",
"taxfree_ustid_checked"=>"hide",
"distributor"=>"Distributor verfügbar",
"distributor_email"=>"Distributor eMail-Adresse",
"distributor_url"=>"Distributor URL",
"iso3"=>"ISO-Code (3-stellig)",
);
/*
	-End-
*/

if (empty($_POST["taxmode"])){
		$_POST["taxfree"] = "0";
		$_POST["taxfree_ustid"] = "0";
		$_POST["taxfree_ustid_checked"] = "0";
		unset($_POST["withtax"]);
}else{
	switch ($_POST["taxmode"]){
		case 1:
			$_POST["taxfree"] = "1";
			$_POST["taxfree_ustid"] = "0";
			$_POST["taxfree_ustid_checked"] = "0";
			break;
		case 2:
			$_POST["taxfree"] = "0";
			$_POST["taxfree_ustid"] = "1";
			$_POST["taxfree_ustid_checked"] = "0";
			break;
		case 3:
			$_POST["taxfree"] = "0";
			$_POST["taxfree_ustid"] = "0";
			$_POST["taxfree_ustid_checked"] = "1";
			break;
	}
}
		
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$tmp_field_value = mysql_real_escape_string($_POST[$row["Field"]]);
			$updateSQL[] = "{$row["Field"]} = '{$tmp_field_value}'";
		}
}
$updateSQL = implode(",",$updateSQL);
//echo $updateSQL;
// Building update query
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$insertHead[] = "{$row["Field"]}";
			$tmp_field_value = mysql_real_escape_string($_POST[$row["Field"]]);
			$valueHead[] = "'{$tmp_field_value}'";
		}
}
#insertHead[] = "`group`";
#$valueHead[] = "7";
$insertHead = implode(",",$insertHead);
$valueHead = implode(",",$valueHead);

		
if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM $valueTable WHERE id={$_GET["delete"]}
	");
	
	$sInform = "$valueName ".$sLang["presettings"]["countries_was_deleted"];
}


if ($_POST["sAction"]=="saveArticle"){
	
	// Check dependencies
		
	if (!$_POST["tax"]) $_POST["tax"] = "0";
	
	if (!$sError){
		if ($_GET["edit"]){
			$sql = "
			UPDATE $valueTable SET 
			$updateSQL
			WHERE id={$_GET["edit"]}
			";
			//echo $sql;
			$insertArticle = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO $valueTable ($insertHead)
			VALUES ($valueHead)
			";
			echo $sql;
			$insertArticle = mysql_query($sql);
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
	
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["countries_Entry_was_saved"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["countries_cant_be_found"];
	}else {		
		$getCustomerGroup = mysql_fetch_array($getSite);
	}
}
?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
</head>

<body >
<?php
$sCore->sInitTranslations(1,"config_countries","true");
?>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteCustomer":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
	}
}

function deleteCustomerGroup(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["countries_should"] ?> <?php echo $valueName ?> "'+text+'" <?php echo $sLang["presettings"]["countries_really_be_deleted"] ?>',window,'deleteCustomer',ev);
	}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
};
</script>



<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["countries_Creating"] : "$valueName ".$sLang["presettings"]["countries_edit"] ?></legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		
		
		$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
		
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  if (preg_match("/taxfree/",$fieldName)) continue;
		   	  
		   	   
		   	   	if ($fieldName=="notice"){
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"$fieldName\" id=\"$fieldName\" style=\"height:225px;width:250px\">{$getCustomerGroup[$row["Field"]]}</textarea>";
		   	   		if ($_GET["edit"]){
		   	   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_countries","{$getCustomerGroup["id"]}");
			   	   	}
			   	   	echo "</li>";
		   	   	}elseif ($fieldName=="countryname"||$fieldName=="active"){
		   	   		echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" id=\"$fieldName\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" />";
		   	   		 if ($_GET["edit"]){
		   	   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_countries","{$getCustomerGroup["id"]}");
			   	   	 }
			   	   	echo "</li>";
		   	   	}
		   	   	else {
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
		   	   	}
			   	   
			   	   echo "<li class=\"clear\"/>";
		   	}

	   }
	   
	   

		
		?>	
		<!-- // Felder ausgeben -->
		<li id="taxfree_0"><label style="width:150px; text-align:left" for="name">Immer Brutto beliefern</label>
		<input type="radio" name="taxmode" value="0" <?php if (empty($getCustomerGroup["taxfree"]) && empty($getCustomerGroup["taxfree_ustid"]) && empty($getCustomerGroup["taxfree_ustid_checked"])){echo "checked";}?>></li><li class="clear"/>
		<li id="taxfree_0"><label style="width:150px; text-align:left" for="name">Immer Netto beliefern</label>
		<input type="radio" name="taxmode" value="1" <?php echo !empty($getCustomerGroup["taxfree"]) ? "checked" : "" ?>></li><li class="clear"/>
		<li id="taxfree_0"><label style="width:150px; text-align:left" for="name">Netto wenn UST-ID eingegeben wurde</label>
		<input type="radio" name="taxmode" value="2" <?php echo (!empty($getCustomerGroup["taxfree_ustid"])||!empty($getCustomerGroup["taxfree_ustid_checked"])) ? "checked" : "" ?>><li class="clear"/></li>
		
		
		</ul>
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["countries_save"] ?></div></button>
			</li>	
		</ul>
	</div>
		</fieldset>
		</form>
<?php
}
?>
		
		


        
        
        
<fieldset class="col2_cat2">
<legend><?php echo $sLang["presettings"]["countries_added_countries"] ?></legend>

<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
			// Query Related-Articles
			$sql = "
			SELECT id, $valueDescription as description FROM $valueTable $valueWhere ORDER BY countryname ASC
			";
			//echo $sql;
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				
				
				
				
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			
				
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons/world.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?> </th>
       <td class="last-child"><?php echo $delete ?><a href="<?php echo $_SERVER["PHP_SELF"]."?edit=".$article["id"]?>" style="cursor:pointer" class="ico pencil"></a></td>
     </tr>
	 	<?php
			}
		?>
   </tbody>
</table>
</fieldset>
<?php
include("../../../backend/elements/window/translations.htm");
?>
<?php
if ($valueAdd){
?>
	<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?new=1" ?>" class="bt_icon world" style="text-decoration:none;"><?php echo $valueName ?> <?php echo $sLang["presettings"]["countries_Creating"] ?></a></li>	
		
		</ul>
		</div>
		<br/><div class="fixfloat"></div><br/>
		

<?php
}
?>
<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>