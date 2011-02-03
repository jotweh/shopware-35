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

$valueName = $sLang["presettings"]["cronjobs_cronjobs"];
$valueDelete = false;
$valueTable = "s_crontab";
$valueAdd = false;
$valueDescription = "name";

$substitute = $sLang["presettings"]["cronjobs_array"];
		
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$updateSQL[] = "`{$row["Field"]}` = '{$_POST[$row["Field"]]}'";
		}
}
$updateSQL = implode(",",$updateSQL);
//echo $updateSQL;
// Building update query
$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
while ($row = mysql_fetch_assoc($getFields)) {
		if ($substitute[$row["Field"]]!="hide"){
			$insertHead[] = "`{$row["Field"]}`";
			$valueHead[] = "'".mysql_real_escape_string(stripcslashes($_POST[$row["Field"]]))."'";
		}
}



$insertHead[] = "`group`";
$valueHead[] = "7";
$insertHead = implode(",",$insertHead);
$valueHead = implode(",",$valueHead);

		
if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM $valueTable WHERE id={$_GET["delete"]}
	");
	
	$sInform = "$valueName ".$sLang["presettings"]["cronjobs_was_deleted"];
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
			$insertArticle = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO $valueTable ($insertHead)
			VALUES ($valueHead)
			";
			echo $sql;
			$insertArticle = mysql_query($sql);
		}
		
		if ($insertArticle){
			$sInform = $sLang["presettings"]["cronjobs_Entry_was_saved"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["cronjobs_cant_be_found"];
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["cronjobs_should"] ?> <?php echo $valueName ?> "'+text+'" <?php echo $sLang["presettings"]["cronjobs_really_be_deleted"] ?>',window,'deleteCustomer',ev);
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
<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $PHP_SELF."?new=1" ?>" class="bt_icon time" style="text-decoration:none;"><?php echo $valueName ?> <?php echo $sLang["presettings"]["cronjobs_create"] ?></a></li>	
		
		</ul>
		</div>
		<br/><div class="fixfloat"></div><br/>


<?php
}
?>


<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["cronjobs_create"] : "$valueName ".$sLang["presettings"]["cronjobs_edit"] ?></legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		
		
		$getFields = mysql_query("SHOW COLUMNS FROM $valueTable");
		
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	//if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		  	
		   	   if ($substitute[$row["Field"]]&&$substitute[$row["Field"]]!="hide"){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	  
		   	  	if($fieldName=="elementID"||$fieldName=="id"||$fieldName=="end"||$fieldName=="start")
		   	  	{
		   	  		
		   	  	}
		   	   	elseif ($fieldName=="start"||$fieldName=="action"||$fieldName=="name"||$fieldName=="end"){
		   	   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
				   echo "<span id=\"{$fieldName}\" style=\"height:25px;width:250px\" class=\"w200\">{$getCustomerGroup[$row["Field"]]}</span></li>";
		   	   	   echo "</li>";
		   	   	}elseif($fieldName=="data")
		   	   	{
		   	   		 echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label>";
		   	   		 echo "<textarea id=\"{$fieldName}\" style=\"height:100px;width:250px;font-size:11px;\" class=\"w200\">".(str_replace("  "," ",print_r(unserialize($getCustomerGroup[$row["Field"]]),true)))."</textarea></li>";
		   	   	  	 echo "</li>";
		   	   	}
		   	   	else
		   	   	{
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
		   	   	}
			   	   
			   	   echo "<li class=\"clear\"/>";
		       
		      

		   //	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->
	<li>	
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"> <?php echo $sLang["presettings"]["cronjobs_save"] ?></div></button></li>	
		
					
		</ul>
		</div>	
		</li>
		
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		
		


        
        
        
<fieldset class="col2_cat2">
<legend><?php echo $sLang["presettings"]["cronjobs_Existing_Cronjobs"] ?></legend>

<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		 <?php	
			// Query Related-Articles
			$sql = "
			SELECT id, $valueDescription as description FROM $valueTable $valueWhere ORDER BY name ASC
			";
			//echo $sql;
			$getArticles = mysql_query($sql);
			while ($article = mysql_fetch_array($getArticles)){
				
				
				
				
					//$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			
				
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons/time.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?> </th>
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

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>