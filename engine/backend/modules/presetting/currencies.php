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

$valueName = $sLang["presettings"]["currencies_Currency"];
$valueDelete = false;
$valueTable = "s_core_currencies";
$valueAdd = false;
$valueDescription = "name";
$substitute = $sLang["presettings"]["currencies_array"];
		
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$updateSQL[] = "`{$row["Field"]}` = '{$_REQUEST[$row["Field"]]}'";
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
	
	$sInform = "$valueName ".$sLang["presettings"]["currencies_was_deleted"];
}

if ($_REQUEST["sAction"]=="saveArticle"){
	// Check dependencies
		
	//if (!$_REQUEST["tax"]) $_REQUEST["tax"] = "0";
	
	//if (!$sError){
		if ($_REQUEST["edit"]){
			$sql = "
			UPDATE $valueTable SET 
			$updateSQL
			WHERE id={$_REQUEST["edit"]}
			";
			//echo $sql;
			$insertArticle = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO $valueTable ($insertHead)
			VALUES ($valueHead)
			";
			$insertArticle = mysql_query($sql);
		}
		/*echo $sql;
		echo mysql_error();
		*/
		
	
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["currencies_entry_saved"];
		}else {
			
		}
	//}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["currencies_cant_be_found"];
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["currencies_should"] ?> <?php echo $valueName ?> "'+text+'" <?php echo $sLang["presettings"]["currencies_really_be_deleted"] ?>',window,'deleteCustomer',ev);
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
if ($valueAdd){
?>

		<div class="buttons" id="buttons">
		<ul>
		
			
		<li id="buttonTemplate" class="buttonTemplate"><a  href="<?php echo $PHP_SELF."?new=1" ?>" class="bt_icon money" value="send"><?php echo $valueName ?> <?php echo $sLang["presettings"]["currencies_create"] ?></a></li>	
		
		</ul>
		</div>
		<div class="clear" style="height:10px;"></div>

<a class="ico add" style="cursor:pointer" href="<?php echo $PHP_SELF."?new=1" ?>"></a><?php echo $valueName ?> <?php echo $sLang["presettings"]["currencies_create"] ?>
<?php
}
?>


<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["edit"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset style="margin-top:0">
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["currencies_create"] : "$valueName ".$sLang["presettings"]["currencies_edit"] ?></legend>
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
				
		   	  
		   	   if ($row["Type"]=="int(1)"){
		   	   	echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
		   	   	if ($getCustomerGroup[$row["Field"]]){
		   	   		$selYes = "selected";
		   	   		$selNo = "";
		   	   	}else {
		   	   		$selYes = "";
		   	   		$selNo = "selected";
		   	   	}
		   	   	echo "<select name=\"{$fieldName}\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">";
		   	   	echo "<option value=\"1\" $selYes>".$sLang["presettings"]["currencies_yes"]."</option>";
		   	   	echo "<option value=\"0\" $selNo>".$sLang["presettings"]["currencies_no"]."</option>";
		   	   	echo "</select>";
		   	   	echo "</li>";
		   	    echo "<li class=\"clear\"/>";
		   	   }
		   	   
		   	   
		   	   else {
		   	   
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
			   	    echo "<li class=\"clear\"/>";
		   	   }
		       
		        
		      
		       
		      

		   	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->
			<li class="clear"></li>	
				<div class="buttons" id="buttons">
		<ul>
		
			
			<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["currencies_save"] ?></div></button></li>	
		
		</ul>
		</div>
		<li class="clear"></li>		
		</ul>
		</fieldset>
		</form>
<?php
}
?>

<?php
if ($_GET["refreshRates"]){
$fetchURL = "http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml";
$xml = file_get_contents($fetchURL);
$parser = xml_parser_create();
xml_parse_into_struct($parser,$xml,$array);
foreach ($array as $key => $value){
	if ($value["tag"]=="CUBE"){
		if ($value["attributes"]["CURRENCY"] && $value["attributes"]["RATE"]){
			$rates[$value["attributes"]["CURRENCY"]] = $value["attributes"]["RATE"];
		}
	}
}

if (!count($rates)){
	echo "<strong>".$sLang["presettings"]["currencies_PRICES_COULD_NOT_BE_UPDATED"]."</strong>";
}else {
	echo $sLang["presettings"]["currencies_Courses_updated"]." - ".date("d.m.Y H:i:s");
	foreach ($rates as $rateKey => $rateValue){
		if ($rateValue){
			$updateRate = mysql_query("
			UPDATE s_core_currencies SET factor = $rateValue WHERE currency='$rateKey'
			");
		}
	}
}
}




?>




<fieldset class="col2_cat2" style="margin-top:0">
<legend><?php echo $sLang["presettings"]["currencies_created_currencies"] ?></legend>
	<div class="buttons" id="buttons">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"]."?new=1" ?>" class="bt_icon money" style="text-decoration:none;" value="send"><?php echo $sLang["presettings"]["currencies_New_Currency"] ?></a></li>	
		
		</ul>
		</div>
		<div class="clear"></div><br/>
<p><img src="../../../backend/img/default/icons/information.png" style="margin:0 15px 0 0;" /><?php echo $sLang["presettings"]["currencies_please_edit_this_data_only"] ?></p>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
			// Query Related-Articles
			$sql = "
			SELECT id, name,standard FROM $valueTable ORDER BY id ASC
			";
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				if (!$article["standard"]){
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["name"]}')\"></a>";			
				}
			?>
		     <tr class="rowcolor2">
		       <th class="first-child"><img src="../../../backend/img/default/icons/money.png" style="margin:0 15px 0 0;" /><?php echo $article["name"]?></th>
		       <td class="last-child"><?php echo $delete ?><a href="<?php echo $_SERVER["PHP_SELF"]."?edit=".$article["id"]?>" style="cursor:pointer" class="ico pencil"></a></td>
	     	</tr>
		 	<?php
				}
			?>
   </tbody>
</table>
<a class="ico3 chart_line" style="width:150px;cursor:pointer" href="<?php echo $_SERVER["PHP_SELF"]."?refreshRates=1"?>"><?php echo $sLang["presettings"]["currencies_update_Courses"] ?></a>
</fieldset>

</body>

</html>