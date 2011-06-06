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

function recursive_delete($id)
{
	$sql = "
		DELETE FROM s_articles_categories WHERE categoryID=$id
	";
	mysql_query($sql);
	$sql = "
		DELETE FROM s_categories WHERE id=$id
	";
	mysql_query($sql);

	$getChilds = mysql_query("
		SELECT id FROM s_categories WHERE parent=$id
	");
	while ($child=mysql_fetch_array($getChilds)){
		recursive_delete($child["id"]);
	}
}

if (isset($_GET["delete"]))
{
	recursive_delete($_GET["delete"]);
	$sInform = "Kategorie wurde gelöscht";
	$action = "parent";
}

if (isset($_POST["categoryName"])){
	if (!$_GET["id"]){
		$_GET["id"] = 1;
	}
	$_POST["categoryName"] = mysql_real_escape_string($_POST["categoryName"]);
	$sql = "
	INSERT INTO s_categories (parent, description,template,active,ac_attr1,ac_attr2,ac_attr3,ac_attr4,ac_attr5,ac_attr6,blog, showfiltergroups)
	VALUES ({$_GET["id"]},'{$_POST["categoryName"]}','".$sCore->sCONFIG['sCATEGORY_DEFAULT_TPL']."',1,
	'{$_POST["attr1"]}','{$_POST["attr2"]}','{$_POST["attr3"]}','{$_POST["attr4"]}','{$_POST["attr5"]}','{$_POST["attr6"]}','{$_POST["blog"]}','{$_POST["showfiltergroups"]}'
	)
	";
	$insertCategory = mysql_query($sql);
	
	echo mysql_error();
	if ($insertCategory){
		$sInform = $sLang["categories"]["categoryedit_Category"]." \"{$_POST["categoryName"]}\" ".$sLang["categories"]["categoryedit_has_been_created"];
	}else {
		$sError = $sLang["categories"]["categoryedit_category_cant_be_created"];
	}
	
	$action = "current";
}

if (isset($_POST["submitContent"])){
	$_POST["cmstext"] = mysql_real_escape_string($_POST["cmstext"]);
	$_POST["cmsheadline"] = mysql_real_escape_string($_POST["cmsheadline"]);
	$_POST["description"] = mysql_real_escape_string($_POST["description"]);
	$_POST["noviewselect"] = empty($_POST["noviewselect"]) ? 0 : 1;
	$sql = "UPDATE s_categories
	SET
	description = '{$_POST["description"]}',
	metakeywords='{$_POST["metakeywords"]}',
	metadescription='{$_POST["metadescription"]}',
	cmsheadline='{$_POST["cmsheadline"]}',
	cmstext='{$_POST["cmstext"]}',
	template='{$_POST["categoryTemplate"]}',
	noviewselect='{$_POST["noviewselect"]}',
	active='{$_POST["active"]}',
	ac_attr1='{$_POST["attr1"]}',
	ac_attr2='{$_POST["attr2"]}',
	ac_attr3='{$_POST["attr3"]}',
	ac_attr4='{$_POST["attr4"]}',
	ac_attr5='{$_POST["attr5"]}',
	ac_attr6='{$_POST["attr6"]}',
	blog = '{$_POST["blog"]}',
	showfiltergroups = '{$_POST["showfiltergroups"]}',
	external = '{$_POST["external"]}',
	hidefilter = '{$_POST["hidefilter"]}',
	hidetop = '{$_POST["hidetop"]}'
	WHERE id={$_GET["id"]}
	";
	if (!empty($_POST["enableGroups"]) && !empty($_GET["id"])){
		/*foreach ($_POST["enableGroups"] as $scID){
			
		}*/
		$sqlAvoid = "SELECT id FROM s_core_customergroups WHERE id NOT IN (".implode(",",$_POST["enableGroups"]).")";
		
		$querySC = mysql_query($sqlAvoid);
		$deletePreviousSC = mysql_query("
		DELETE FROM s_categories_avoid_customergroups WHERE categoryID = {$_GET["id"]}
		");
		while ($insertSC = mysql_fetch_assoc($querySC)){
			$sqlInsert = "
			INSERT INTO s_categories_avoid_customergroups (categoryID,customergroupID)
			VALUES (
			{$_GET["id"]},
			{$insertSC["id"]}
			)
			";
			
			$insertSCRow = mysql_query($sqlInsert);
		}
	}
	$updateCategory = mysql_query($sql);
	if ($updateCategory){
		$sInform = $sLang["categories"]["categoryedit_Category"]." \"{$_POST["categoryName"]}\" ".$sLang["categories"]["categoryedit_has_been_updated"];
	}else {
		$sError = $sLang["categories"]["categoryedit_Category_could_not_be_updated"];
	}
}

if ($_GET["id"]){
	$getCategory = mysql_query("
	SELECT * FROM s_categories
	WHERE id={$_GET["id"]}
	");
	
	if (@mysql_num_rows($getCategory)){
		$category = mysql_fetch_array($getCategory);
		// Collect some information
		$result = mysql_query("
			SELECT COUNT(*) FROM s_articles_categories WHERE categoryID={$_GET["id"]}
		");
		if($result&&mysql_num_rows($result))
			$numberArticles = mysql_result($result,0,0);
		else
			$numberArticles = 0;
		// Sub-Categories
		
	}
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo $sLang["categories"]["categoryedit_Reorder_TreePanel"] ?></title>
<!-- Common Styles for the examples -->
</head>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

<body style="padding: 25px 0">
<script>
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteCategory":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
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
		if ($sInform && !preg_match("/aktualisiert/",$sInform)){
			echo "parent.parent.Growl('$sInform');";
			switch ($action){
				case "current":
					echo "parent.myExt.reload();";
					break;
				case "parent":
					echo "parent.myExt.reloadParent();";
					break;
				default:
					echo "parent.myExt.reload();";	
					break;
			}
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
function deleteCategory(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["categories"]["categoryedit_Should_the_category"] ?> "'+text+'" <?php echo $sLang["categories"]["categoryedit_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>
<?php
	if(!empty($_GET['settings'])){
?>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		// General options
		mode: "exact",
		elements : "HTMLdescription",
		theme : "advanced",
		<?php echo$sCore->sCONFIG['sTINYMCEOPTIONS']?>, 
		extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]",
		plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template,imagemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor|,insertimage"	
	});
	
</script>
<?php
	}
?>
<?php
if(empty($_GET['settings'])){
?>
<?php
// Check if systemcategory
$checkCategory = mysql_query("
SELECT id FROM s_core_multilanguage WHERE parentID={$category["id"]}
");
if (@mysql_num_rows($checkCategory)){
	$systemcategory = true;
}
?>
<fieldset class="col2_cat2">
<legend><a class="ico help"></a><?php echo $sLang["categories"]["categoryedit_Categories_rename_move"] ?></legend>
<?php echo $sLang["categories"]["categoryedit_To_rename_a_category"] ?><br /><br />
<?php echo $sLang["categories"]["categoryedit_To_add_a_category_to_move"] ?>
<br /><br />
<?php
if ($systemcategory){
?>
<strong><?php echo $sLang["categories"]["categoryedit_Important_Note"] ?></strong><br /><p style="color:#F00">
<?php echo $sLang["categories"]["categoryedit_This_category_is_a_Systemcategory"] ?></p>
<?php
}
?>
</fieldset>
<div class="clear"></div>
<?php
	}
?>
<?php
if (count($category)){
?>
<?php
	if(empty($_GET['settings'])){
?>
<fieldset class="col2_cat2">
<legend><a class="ico folder_add"></a> <?php echo $sLang["categories"]["categoryedit_Category"] ?> <?php echo $category["description"] ?></legend>
<div style="">System-ID: <?php echo $_GET["id"] ?></div>
<div style=""><?php echo $sLang["categories"]["categoryedit_Assigned_Article"] ?> <?php echo $numberArticles ? $numberArticles : "0"?></div><br />
	<form name="newSubcategory" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<ul>
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_New_subcategory"] ?></label>
	<input name="categoryName" type="text" id="txtName" class="w150 h24" value="" />
	</li><li class="clear"/>
	<li><strong><?php echo $sLang["categories"]["categoryedit_After_creating_the_category"] ?></strong></li>
	<li class="clear"/>
	</ul>
	
	</form>
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["categories"]["categoryedit_creating"] ?></div></button></li>	
		</ul>
	</div>
</fieldset>
<?php
if (!$systemcategory||!empty($numberArticles)){
?>
<fieldset class="col2_cat2">
<legend><a class="ico delete"></a> Kategorie: <?php echo $category["description"] ?> löschen</legend>
<div><p>Dieser Kategorie sind <?php echo $numberArticles ? $numberArticles : "0"?> Artikel zugeordnet</p></div>

	<div class="buttons" id="buttons">
		<ul>
		<?php
		$descr = $category["description"];
		$descr = str_replace("'","",$descr);
		$descr = str_replace("\"","",$descr);
		
		?>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $category["id"] ?>,'<?php echo $descr ?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["categories"]["categoryedit_delete"] ?></div></button></li>	
		</ul>
	</div>
</fieldset>

<?php
}
?>
<?php
} else {
?>
<div style="clear:both"></div><br />
<fieldset class="col2_cat2">
<legend><a class="ico page"></a> <?php echo $sLang["categories"]["categoryedit_more_options"] ?></legend>
	<form name="editCategoryProperties" method="POST" id="contentForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&settings=1">
	<input type="hidden" name="submitContent" value="1">
	<!-- Template Auswahl -->
	<?php

	$templates = $sCore->sCONFIG['sCATEGORYTEMPLATES'];
	$templates = explode(";",$templates);
	
	if ($category["parent"]!=1 && empty($category["blog"])){
		// Template-Auswahl für Startseite deaktivieren
	?>
		<ul>
		<li><label for="name">Auswahl Listing:</label>
		<select name="categoryTemplate" class="w150 h24" value="" >
		<?php
		foreach ($templates as $template){
			$template = explode(":",$template);
			if ($category["template"]==$template[0]){
				$selected = "selected";
			}else {
				$selected = "";
			}
			echo "<option value=\"{$template[0]}\" $selected>{$template[1]}</option>";
		}
		?>
		</select>
		</li>
		<li class="clear"/>
		</ul>
	<?php
	}
	if ($sCore->sCheckLicense("","",$sCore->sLicenseData["sGROUPS"]) || 1 == 1){
	?>
		<p style="clear:both"></p>
		<!-- //  Template Auswahl -->
		<fieldset style="margin-top: -10px;position:relative;top:0;left:0;width:600px;">
		<legend>Aktiv für Kundengruppen:</legend>
		<p>Achtung! Diese Einstellung betrifft nicht die zugeordneten Artikel. Falls Sie diese auch sperren möchten,
		müssen Sie die Konfiguration jeweils in den Artikelstammdaten vornehmen.
		</p>
		<ul>
		<?php
		$sql = "SELECT sc.id,sc.description, IF(s_categories_avoid_customergroups.categoryID,1,0) AS active FROM s_core_customergroups sc
		LEFT JOIN s_categories_avoid_customergroups ON s_categories_avoid_customergroups.categoryID = {$_GET["id"]} AND customergroupID = sc.id
		ORDER BY sc.id ASC
		";
		
		$queryGroups = mysql_query($sql);
		while ($sc = mysql_fetch_assoc($queryGroups)){
		?>
			<li>
				<input id="chkGrp<?php echo $sc["id"]?>" type="checkbox" name="enableGroups[]" value="<?php echo $sc["id"]?>" <?php if (empty($sc["active"])) echo "checked"; ?> >
				<label for="chkGrp<?php echo $sc["id"]?>" style="margin-left:5px;width:70px"><?php echo $sc["description"] ?></label>
			</li>
		<?php
		}
		?>
		<input type="hidden" name="enableGroupsHidden" value="1">
		</ul>
		</fieldset>
	<?php
	}
	?>
	<ul>
	
	<li><label for="name">Kategorie-Bezeichnung:</label>
	<input name="description" type="text" class="w150 h24" value="<?php echo htmlentities($category["description"], null, null, false) ?>" />
	</li><li class="clear"/>
	<li><label for="name">Aktiv:</label>
	<input name="active" type="checkbox"  value="1" <?php echo $category["active"] ? "checked" : ""?> />
	</li><li class="clear"/>
	<li><label for="name">Kategorie NICHT in Top-Navigation anzeigen:</label>
	<input name="hidetop" type="checkbox"  value="1" <?php echo $category["hidetop"] ? "checked" : ""?> />
	</li><li class="clear"/>
	
	
	<li><label for="name">Darstellungswechsel im Listing deaktivieren:</label>
	<input name="noviewselect" type="checkbox"  value="1" <?php echo $category["noviewselect"] ? "checked" : ""?> />
	</li><li class="clear"/>
	<li><label for="name">Auf externe Seite verlinken:</label>
	<input name="external" type="text" class="w150 h24" value="<?php echo $category["external"] ?>" />
	</li><li class="clear"/>
	<li><label for="name">Blog-Kategorie:</label>
	<input name="blog" type="checkbox"  value="1" <?php echo $category["blog"] ? "checked" : ""?> />
	</li>
	<li class="clear"/>
	<li><label for="showfiltergroups">Filter gruppiert anzeigen:</label>
	<input name="showfiltergroups" type="checkbox"  value="1" <?php echo $category["showfiltergroups"] ? "checked" : ""?> />
	</li>
	<li class="clear"/>
	<li><label for="showfiltergroups">Filter ausblenden:</label>
	<input name="hidefilter" type="checkbox"  value="1" <?php echo $category["hidefilter"] ? "checked" : ""?> />
	</li>
	<li class="clear"/>
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_Meta-Keywords"] ?></label>
	<input name="metakeywords" type="text" class="w150 h24" value="<?php echo htmlentities($category["metakeywords"], null, null, false) ?>" />
	</li>
	<li class="clear"/>
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_Meta-Description"] ?></label></li><li class="clear"/>
	<li>
	<textarea name="metadescription" cols="20" rows="20" style="margin-left: 160px; width: 350px; height:80px; font-size: 11px; font-family: Verdana, Arial, Helvetica, sans-serif;"><?php echo $category["metadescription"] ?></textarea>
	</li>
	<li class="clear"/>
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_title"] ?></label>
	<input name="cmsheadline" type="text" id="txtName" class="w200 h24" value="<?php echo htmlentities($category["cmsheadline"], null, null, false) ?>" />
	</li>
	<li class="clear"/>
	
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_Description"] ?></label>
	<div style="float:left">
	<textarea name="cmstext" id="HTMLdescription" cols="5" rows="5" mce_editable="true" style="float: left; width: 150px; height:80px; font-size: 11px; font-family: Verdana, Arial, Helvetica, sans-serif;"><?php echo htmlentities($category["cmstext"]) ?></textarea>
	</div>
	</li>
	<li class="clear"/>
	<?php
	for ($i=1;$i<=6;$i++){
	?>
	<li><label for="name">Freitext <?php echo $i ?></label>
	<input name="attr<?php echo $i ?>" type="text" id="txtName" class="w150 h24" value="<?php echo htmlentities($category["ac_attr$i"], null, null, false) ?>" />
	</li>
	<li class="clear"/>
	<?php
	}
	?>
	</ul>
	</form>
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('contentForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["categories"]["categoryedit_save"] ?></div></button></li>	
		</ul>
	</div>
</fieldset>
<div class="clear"></div>
<?php
}
?>
<?php
} else {
?>
<fieldset class="col2_cat2" style="margin-top:0;margin-bottom:5px;width:90%">
<legend><a class="ico folder_add"></a><?php echo $sLang["categories"]["categoryedit_create_new_category"] ?></legend>
<form name="newSubcategory" method="POST"  id="ourForm"  action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<ul>
	<li><label for="name"><?php echo $sLang["categories"]["categoryedit_new_maincategory"] ?></label>
	<input name="categoryName" type="text" id="txtName" class="w150 h24" value="" /></li>
	<li class="clear"/>
	</ul>
	</form>
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["categories"]["categoryedit_creating"] ?></div></button></li>	
		</ul>
	</div>
</fieldset>

<?php
}
?>

<div class="clear"></div>
</body>
</html>
