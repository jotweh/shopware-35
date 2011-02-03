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

$valueName = $sLang["presettings"]["units_Price_unit"];
$valueDelete = true;
$valueTable = "s_core_units";
$valueAdd = true;
$valueDescription = "description";
$valueWhere = "WHERE id>=1";
$substitute = $sLang["presettings"]["units_array"];
		
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$updateSQL[] = "{$row["Field"]} = '{$_POST[$row["Field"]]}'";
		}
}
$updateSQL = implode(",",$updateSQL);
//echo $updateSQL;
// Building update query
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$insertHead[] = "{$row["Field"]}";
			$valueHead[] = "'{$_POST[$row["Field"]]}'";
		}
}
$insertHead = implode(",",$insertHead);
$valueHead = implode(",",$valueHead);

		
if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM $valueTable WHERE id={$_GET["delete"]}
	");
	
	$sInform = "$valueName ".$sLang["presettings"]["units_was_deleted"];
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
			//echo $sql;
			$insertArticle = mysql_query($sql);
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
	
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["units_entry_deleted"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["units_cant_be_found"];
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
$sCore->sInitTranslations(1,"config_units","true");
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["units_should"] ?> <?php echo $valueName ?> "'+text+'" <?php echo $sLang["presettings"]["units_really_deleted"] ?>',window,'deleteCustomer',ev);
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
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["units_create"] : "$valueName ".$sLang["presettings"]["units_edit"] ?></legend>
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
		   	   	
		   	   	echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" id=\"$fieldName\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" />";
		   	   	if ($_GET["edit"]){
		   	   	   	echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_units","{$getCustomerGroup["id"]}");
			   	}
		   	   	echo "</li><li class=\"clear\"/>";
		   	}
	   }

		
		?>	
		<!-- // Felder ausgeben -->
				<li class="clear"></li>
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["units_save"] ?></div></button></li>	
		
					
		</ul>
		</div>
		<li class="clear"></li>
		
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		
		


        
        
        
<fieldset class="col2_cat2">
<legend>Verfügbare Datensätze:</legend>
<p><img src="../../../backend/img/default/icons/information.png" style="margin:0 15px 0 0;" /><?php echo $sLang["presettings"]["units_Please_change_this_data_only"] ?></p>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
			// Query Related-Articles
			$sql = "
			SELECT id, $valueDescription as description FROM $valueTable $valueWhere ORDER BY id ASC
			";
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				
				// Check if this group is assigned to any customer
				$queryCustomers = mysql_query("
				SELECT id FROM s_user WHERE customergroup='{$article["groupkey"]}'
				");
				
				if ($valueDelete){
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			
				}else {
					$delete = "";
				}
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons4/basket_full.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?></th>
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
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?new=1" ?>" class="bt_icon basket_full" style="text-decoration:none;"><?php echo $valueName ?> <?php echo $sLang["presettings"]["units_create"] ?></a></li>	
		
		</ul>
		</div>
		<br/><div class="fixfloat"></div><br/>
		
<?php
}
?>


<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>