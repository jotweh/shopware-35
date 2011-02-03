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
//echo "<pre>";
//echo "POST<br>";
//print_r($_POST);
//echo "GET<br>";
//print_r($_GET);
//echo "</pre>";


//Bearbeitungsmodus && Form noch nicht abgeschikt
if ($_GET["edit"] && !isset($_POST["sAction"])){
	$editsql = sprintf("SELECT * FROM `s_core_config_text` WHERE name = '{$_GET["edit"]}' OR id='%s'", $_GET["edit"]);
	$editquery 	= mysql_query($editsql);
	
	$res_group 	= mysql_result($editquery, 0, 'group');
	$res_name 	= mysql_result($editquery, 0, 'name');
	$res_value 	= mysql_result($editquery, 0, 'value');
	$res_description = mysql_result($editquery, 0, 'description');
	
	$group_value = $res_group;
	$description_value = $res_description;
	$value_value = $res_value;
	$name_value = $res_name;
}

//Form ABGESCHICKT
if (isset($_POST["sAction"]))
{
	if($_POST["group_choose"] == "new")
	{
		//Create new Group
		$nextQ = mysql_query(" SELECT MAX( groupID ) +1 AS `next_group_id` FROM `s_core_config_text_groups`");
		$next_group_id = mysql_result($nextQ, 0, 'next_group_id');
		
		$queryInsert = mysql_query("
		INSERT INTO s_core_config_text_groups (description, groupID) VALUES ('{$_POST["sNewGroup"]}', {$next_group_id})
		");
		$group_value = $next_group_id;
	}else{
		$group_value = $_POST["sGroup"];	
	}
	
	//Set Values
	$group_value = $group_value;
	$description_value = $_POST["sDescription"];
	$value_value = $_POST["sValue"];
	$name_value = $_POST["sName"];
	
	$value_value_save = mysql_real_escape_string($value_value);
	//Bearbeitungsmodus
	if(!empty($_GET["edit"]))
	{
		//Update data
		$sql = "
		UPDATE s_core_config_text SET
		`group`='{$group_value}',
		`name`='{$name_value}',
		`value`='{$value_value_save}',
		`description`='{$description_value}'
		WHERE id={$_GET["edit"]}
		";
		mysql_query($sql);
	}
	
	//Neuerstellungsmodus
	if(!empty($_GET["new"]))
	{
		//Update data
		$sql = "
		INSERT INTO s_core_config_text
		(`group`, `name`, `value`, `description`)
		VALUES (
		{$group_value},
		'{$name_value}',
		'{$value_value}',
		'{$description_value}'
		)";
		mysql_query($sql);
		$last_id = mysql_insert_id();
		echo "<meta http-equiv='refresh' content='0; url=".$_SERVER["PHP_SELF"]."?edit=".$last_id."'>";
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

<body>

<script>
function validateFields()
{
	var error = false;
	var focus_id = "";
	
	//Reset
	$('sNewGroup').setStyle('border', '1px solid #cccccc');
	$('sDescription').setStyle('border', '1px solid #cccccc');
	
	//Group Vali
	if($('radio_new').checked == true)
	{
		var newgroupname = $('sNewGroup').value
		if(newgroupname.trim() == "")
		{
			$('sNewGroup').setStyle('border', '1px solid red');
			parent.parent.Growl("<?php echo $sLang["snippets"]["enter_groupname"] ?>");
			$('sNewGroup').value = "";
			if(focus_id == "") focus_id = "sNewGroup";
			error = true;
		}
	}
	
	//Beschreibung Validierung
	var headline = $('sDescription').value;
	if(headline.trim() == "")
	{
		$('sDescription').setStyle('border', '1px solid red');
		parent.parent.Growl("<?php echo $sLang["snippets"]["enter_description"] ?>");
		$('sDescription').value = "";
		if(focus_id == "") focus_id = "sDescription";
		error = true;
	}
	
	//Smartyvarible Validierung
	var headline = $('sName').value;
	if(headline.trim() == "")
	{
		$('sName').setStyle('border', '1px solid red');
		parent.parent.Growl("<?php echo $sLang["snippets"]["enter_smartyvar"] ?>");
		$('sName').value = "";
		if(focus_id == "") focus_id = "sName";
		error = true;
	}
	
	if(error == false)
	{
		return true;
	}else{
		$(focus_id).focus();
		return false;
	}
//	$('ourForm').submit();
}
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
}
</script>

<?php
$sCore->sInitTranslations(1,"config_snippets","true");


if ($_GET["edit"] || $_GET["new"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>&new=<?php echo $_GET["new"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["new"] ? "$valueName ".$sLang["snippets"]["add_snippet_title"] : "$valueName ".$sLang["snippets"]["edit_snippet_title"] ?></legend>
		
		
	
		<!-- Felder ausgeben -->
		<div style="float:left;">
			<input style="position:relative; top:-5px;" type="radio" name="group_choose" value="exist" checked="checked"/>
			<label style="width:60px; text-align:left;" for="name"><?php echo $sLang["snippets"]["group_label"] ?></label>
			<select name="sGroup">
			<?php
			$getGroups = mysql_query("
			SELECT groupID AS id, description FROM s_core_config_text_groups ORDER BY groupID ASC
			");
			while ($group=mysql_fetch_array($getGroups)){
			?>
			<option value="<?php echo $group["id"] ?>" <?php echo $_POST["group"]==$group["id"] || $group_value==$group["id"] ? "selected" : ""?>><?php echo $group["description"]?></option>	
			<?php
			}
			?>
			</select>
		</div>
		
		<div style="float:left;">
			<input id="radio_new" style="position:relative; top:-5px;" type="radio" name="group_choose" value="new">
			<label style="width:60px; text-align:left;" for="name"><?php echo $sLang["snippets"]["group_add_label"] ?></label>
			<input name="sNewGroup" type="text" id="sNewGroup" class="w75" value="<?php echo $_POST["sBannerName"] ?>" />
		</div>
		
		<div style="clear:both; height:15px;"></div>
		
		<label style="width:90px; text-align:left;" for="name"><?php echo $sLang["snippets"]["description_label"] ?></label>
		<input name="sDescription" type="text" id="sDescription" style="width:350px;" value="<?php echo $description_value ?>" />
		
		<div style="clear:both; height:15px;"></div>
		
		<label style="width:90px; text-align:left;" for="name"><?php echo $sLang["snippets"]["smartyvar_label"] ?></label>
		<input name="sName" type="text" id="sName" style="width:350px;" value="<?php echo $name_value ?>" />
		
		<div style="clear:both; height:15px;"></div>
		
		<label style="width:90px; text-align:left;" for="name"><?php echo $sLang["snippets"]["content_label"] ?></label>
		<textarea name="sValue" type="text" id="sValue" style="width:350px; height:200px;"><?php echo htmlentities($value_value); ?></textarea>
		
		<?php 
		if($_GET["edit"])
		{
			echo "<div style='float:left;' id=\"value\"></div>";
			echo $sCore->sBuildTranslation("value","value","1","config_snippets","{$name_value}"); 
		}
		?>
		
		<div style="clear:both; height:15px;"></div>
		
		
		<?php
		if(empty($_GET["edit"]))
		{
			$btn_text = $sLang["snippets"]["save_btn"];
		}else{
			$btn_text = $sLang["snippets"]["save_edit_btn"];
		}
		?>
		<div class="buttons" id="buttons">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate">
				<button onClick="return validateFields();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $btn_text ?></div></button>
				</li>	
			</ul>
		</div>
		</fieldset>
		</form>
<?php
}
?>
		
		       

<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>