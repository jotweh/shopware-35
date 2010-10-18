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

$valueName = $sLang["presettings"]["snippets_Textbrick"];
$valueDelete = true;
$valueTable = "s_core_config_text";
$valueAdd = true;
$valueDescription = "name";

$substitute = $sLang["presettings"]["snippets_array"];
		
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
$insertHead[] = "`group`";
$valueHead[] = "7";
$insertHead = implode(",",$insertHead);
$valueHead = implode(",",$valueHead);

		
if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM $valueTable WHERE id={$_GET["delete"]}
	");
	
	$sInform = "$valueName ".$sLang["presettings"]["snippets_was_deleted"];
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
			$sInform = $sLang["presettings"]["snippets_entry_was_saved"];
		}else {
			
		}
	}
}



if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM $valueTable WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = "$valueName ".$sLang["presettings"]["snippets_cant_be_found"];
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["presettings"]["snippets_should"] ?> <?php echo $valueName ?> "'+text+'" <?php echo $sLang["presettings"]["snippets_really_be_deleted"] ?>',window,'deleteCustomer',ev);
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
$sCore->sInitTranslations(1,"config_snippets","true");
?>
<?php
if ($valueAdd){
?>
<a class="ico add" style="cursor:pointer" href="<?php echo $PHP_SELF."?new=1" ?>"></a><?php echo $valueName ?> <?php echo $sLang["presettings"]["snippets_create"] ?>
<?php
}
?>

<br />
<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["presettings"]["snippets_create"] : "$valueName ".$sLang["presettings"]["snippets_edit"] ?></legend>
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
				
		   	  
		   	  
		   	   
		   	   	if ($fieldName=="value"){
		   	   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"$fieldName\" id=\"$fieldName\" style=\"height:325px;width:500px\">{$getCustomerGroup[$row["Field"]]}</textarea>";
		   	   	   if ($_GET["edit"]){
		   	   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_snippets","{$getCustomerGroup["name"]}");
		   	   	   }
		   	   	   echo "</li>";
		   	   	}else {
			   	   echo "<li id=\"$fieldName\"><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\"  style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getCustomerGroup[$row["Field"]]}\" /></li>";
		   	   	}
			   	   
			   	   echo "<li class=\"clear\"/>";
		   	   
		       
		        
		      
		       
		      

		   	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->
		
		
		<li><a class="ico add" style="cursor:pointer" onClick="$('ourForm').submit();"></a> <?php echo $sLang["presettings"]["snippets_save"] ?></li>
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		
		
		<br />

        
        
        
<fieldset class="col2_cat2">
<legend><?php echo $sLang["presettings"]["snippets_created_Textbricks"] ?></legend>

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
				
				
				
				
					$delete = "<a style=\"cursor:pointer\" class=\"ico delete\" onclick=\"deleteCustomerGroup({$article["id"]},'{$article["description"]}')\"></a>";			
				
		?>
        
     <tr class="rowcolor2">
       <th class="first-child"><img src="../../../backend/img/default/icons/group.png" style="margin:0 15px 0 0;" /><?php echo $article["description"]?> </th>
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