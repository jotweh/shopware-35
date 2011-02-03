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


if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM s_cms_static WHERE id={$_GET["delete"]}
	");
	
	$sInform = $sLang["cmsstatic"]["cms_site_deleted"];
}


if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
		
		
	if (!$_POST["description"]) $sError = $sLang["cmsstatic"]["cms_enter_title"];
	if (!$_POST["grouping"]) $sError = "Keine Gruppe ausgewählt";
	
	if (!$sError){
		$_POST["html"] = mysql_real_escape_string($_POST["html"]);
		
		$_POST["description"] = mysql_real_escape_string($_POST["description"]);
		$grouping = implode("|",$_POST["grouping"]);
		
		if ($_POST["groupNew"]){
			$_POST["groupNew"] = mysql_real_escape_string($_POST["groupNew"]);
			// Insert group
		}
		if ($_GET["edit"]){
			
			
			$sql = "
			UPDATE s_cms_static 
			SET
			tpl1variable='{$_POST["tpl1variable"]}',
			tpl1path='{$_POST["tpl1path"]}',
			tpl2variable='{$_POST["tpl2variable"]}',
			tpl2path='{$_POST["tpl2path"]}',
			tpl3variable='{$_POST["tpl3variable"]}',
			tpl3path='{$_POST["tpl3path"]}',
			description='{$_POST["description"]}',
			html='{$_POST["html"]}',
			grouping='$grouping',
			position='{$_POST["position"]}',
			link='{$_POST["link"]}',
			target='{$_POST["target"]}'
			WHERE id={$_GET["edit"]}
			";
			//echo $sql;
			$insertArticle = mysql_query($sql);
			
			
			/*$insertArticle = mysql_query("
			UPDATE s_cms_content SET
			description='{$_POST["sCmsHeadline"]}',
			text='{$_POST["sCmsText"]}',
			link='{$_POST["sCmsLink"]}',
			datum='{$_POST["sCmsDatum"]}'
			$picture
			$file
			WHERE id={$_GET["edit"]}
			");*/
			
		}else {
			$sql = "
			INSERT INTO s_cms_static (tpl1variable,tpl1path,
			tpl2variable,tpl2path,
			tpl3variable,tpl3path,description, html, grouping, position,link,target)
			VALUES (
			'{$_POST["tpl1variable"]}',
			'{$_POST["tpl1path"]}',
			'{$_POST["tpl2variable"]}',
			'{$_POST["tpl2path"]}',
			'{$_POST["tpl3variable"]}',
			'{$_POST["tpl3path"]}',
			'{$_POST["description"]}',
			'{$_POST["html"]}',
			'$grouping',
			'{$_POST["position"]}',
			'{$_POST["link"]}',
			'{$_POST["target"]}'
			)
			
			";
			//echo $sql;
			$insertArticle = mysql_query($sql);
			$_GET["edit"] = mysql_insert_id();
		}
		
		if ($insertArticle){
			$sInform = $sLang["cmsstatic"]["cms_Entry_saved"];
		}
	}else {
		$_GET["new"] = 1;
	}
}

if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_cms_static WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["cmsstatic"]["cms_article_not_found"];
	}else {		
		$getSite = mysql_fetch_array($getSite);
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
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

</head>

<body >

<script language="javascript" type="text/javascript">
  	tinyMCE.init({
		// General options
		mode: "textareas",
		elements : "txtlangbeschreibung",
		theme : "advanced",
		<?php echo$sCore->sCONFIG['sTINYMCEOPTIONS']?>,
		extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]",
		//cleanup : false, skin : "o2k7", relative_urls : false,theme_advanced_resizing : true, theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left",	theme_advanced_path_location : "bottom",
		plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template,imagemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor|,insertimage"	
	});
	
	
</script>

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["cmsstatic"]["cms_should_the_site"] ?> "'+text+'" <?php echo $sLang["cmsstatic"]["cms_really_deleted"] ?>',window,'deleteArticle',ev);
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
		<legend><?php echo $sLang["cmsstatic"]["cms_edit_content"] ?></legend>
		
		<ul>
		
		
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_cms_static");
		
		
		$substitute = $sLang["cmsstatic"]["cms_array"];
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	   if ($fieldName=="html"){
		   	   	  echo "<li><textarea name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">{$getSite[$row["Field"]]}</textarea></li>";
		   	   }
		   	}
	   }

		?>	
		</ul>
		<div class="clear"></div>
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["auth"]["auth_new_save"] ?></div></button>
			</li>	
		</ul>
	</div>
		</fieldset>

		
		<fieldset>
		<legend>Grundeinstellungen</legend>
		<?php
		if ($_GET["edit"]){
		?>
		<strong><?php echo $sLang["cmsstatic"]["cms_HTML_code_to_integrate_the_page"] ?></strong><br />
		<div class="info_box">
		&lt;a href="{url controller=custom sCustom=<?php echo $getSite["id"] ?>}" title="<?php echo $getSite["description"] ?>"&gt;<?php echo $getSite["description"] ?>&lt;/a&gt;
		</div><br />
		<?php
		}
		?>
		<ul>
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_cms_static");
		
		
		$substitute = $sLang["cmsstatic"]["cms_array"];
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	   if ($fieldName=="html"){
		   	   	//  echo "<li>{$column}:<textarea name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\">{$getSite[$row["Field"]]}</textarea></li>";
		   	   }
		   	   elseif ($fieldName=="grouping"){
		   	   	echo "<li><strong>Positionen</strong></li><li class=\"clear\"/>";
		   	   	// Write possible groups
		   	   	$positions = $sCore->sCONFIG["sCMSPOSITIONS"];
				$positions = explode(";",$positions);
				$checkboxValues = $getSite[$row["Field"]];
				$checkboxValues = explode("|",$checkboxValues);
				foreach ($checkboxValues as $checkboxValue) $boxValues[$checkboxValue]=true;
		   	   	foreach ($positions as $position){
					$position = explode(":",$position);
					if ($position[0] && $position[1]){
						if ($boxValues[$position[1]]){
							$checked = " checked";
						}else {
							$checked = "";
						}
						echo "<li class=\"clear\"/><li><label for=\"name\">{$position[0]}:</label>
						<input type=\"checkbox\" name=\"grouping[]\" value=\"{$position[1]}\" $checked>
						</li><li class=\"clear\"/>";
					}
				}
					
				/*echo "<li class=\"clear\"/><li><label for=\"name\">oder neu:</label>
						<input type=\"text\" style=\"height:25px;\" class=\"w200\" name=\"groupNew\">
						</li><li class=\"clear\"/>";
				echo "<li>&nbsp;</li><li class=\"clear\"/>";*/
		   	   }
		   	   else {
		   	   
		   	   echo "<li><label for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"height:25px;\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" /></li>";
		   	   echo "<li class=\"clear\"/>";
		   	   }

		   	}

	   }

		
		?>	
		
		<!-- // Felder ausgeben -->
		
		<li style="clear:both"></li>
		</ul>
		
			
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["auth"]["auth_new_save"] ?></div></button>
			</li>	
		</ul>
	</div>
		
		</fieldset>
		</form>
<?php
}
?>
		
		
