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
if(isset($_GET['hide']))
{
	die();
}
// *****************
//echo "POST";
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
//
//echo "GET";
//echo "<pre>";
//print_r($_GET);
//echo "</pre>";
?>
<?php
function makeProperDate($date){
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
}
function upload($field, $extensions, $path,$imgConvert){
	$filename = $_FILES[$field]['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = strtolower($filename[count($filename)-1]);
	
	if ($extensions){
		if (!in_array($filenameext,$extensions)){
			echo $filenameext;
			return "WRONG FILE";
		}
	}
	
	if ($imgConvert) $filenameext = "jpg";
	
	
	$filename = $filename[0];
	// Random-Part for permit overwrite of existing files
	// (Article-ID | Download-Id)
	$filename = $filename."-".$_GET["id"].rand(0,10000);
	
		
	if (move_uploaded_file($_FILES[$field]['tmp_name'], $path.$filename.".".$filenameext)){
		chmod($path.$filename.".".$filenameext,0775);
		if ($imgConvert){
			$image = imagecreatefromjpeg($path.$filename.".".$filenameext);
			
			$imageWidth = imagesx($image);
			$imageHeight= imagesy($image);
			
			$thbWidth = $imgConvert;
			$thbHeight = $imageHeight / ($imageWidth / $thbWidth);
			
			$thumb = imagecreatetruecolor($thbWidth, $thbHeight);
				
			imagecopyresampled($thumb,$image,0,0,0,0,$thbWidth,$thbHeight,$imageWidth,$imageHeight);
			
			$neue_datei = $path.$filename."Thumb".".".$filenameext;
			imagejpeg($thumb, $neue_datei ,80);
			@chmod($path.$filename.".".$neue_datei,0775);
			return $filename;			
		}else {
			return $filename.".".$filenameext;
		}
		
		
	}else {
		return "ERROR";
	}	
}
?>
<?php

//Gruppen ID suchen, wenn das Formular gerade erst geladen wurde
if(!empty($_GET['edit']) && !isset($_POST['sAction']))
{
	//Id ermitteln
	$strlen = strlen($_GET['edit']);
	$id = substr($_GET['edit'], 3, $strlen-3);
	
	$sql = sprintf("SELECT `groupID` FROM `s_cms_content` WHERE `id` = '%s'", $id);
	$query = mysql_query($sql);
	$group_id = mysql_result($query, 0, 'groupID');
	
	
	

	//Delete image
	if(!empty($_GET['deleteImg']))
	{
		$sql = sprintf("SELECT `img` FROM `s_cms_content` WHERE `id` = '%s'", $id);
		$query = mysql_query($sql);
		$img_src = mysql_result($query, 0, 'img');
		
		if(!empty($img_src))
		{
			$imgPath = "../../../../images/cms/";
			
			if(is_file($imgPath.$img_src.".jpg"))
				unlink($imgPath.$img_src.".jpg");
			
			if(is_file($imgPath.$img_src."Thumb.jpg"))
				unlink($imgPath.$img_src."Thumb.jpg");
				
			
			$sql_up = "UPDATE `s_cms_content` SET `img` = '' WHERE `id` = '{$id}' LIMIT 1 ;";
			mysql_query($sql_up);
		}
	}

	//Delete attachment
	if(!empty($_GET['deleteAttachment']))
	{
		$sql = sprintf("SELECT `attachment` FROM `s_cms_content` WHERE `id` = '%s'", $id);
		$query = mysql_query($sql);
		$attachment_src = mysql_result($query, 0, 'attachment');
		
		if(!empty($attachment_src))
		{
			$attachmentPath = "../../../../files/cms/";
			
			if(is_file($attachmentPath.$attachment_src))
				unlink($attachmentPath.$attachment_src);				
			
			$sql_up = "UPDATE `s_cms_content` SET `attachment` = '' WHERE `id` = '{$id}' LIMIT 1 ;";
			mysql_query($sql_up);
		}
	}
}
	

//Ausgewählte Gruppe
$sCmsNewGroup = trim($_POST['sCmsNewGroup']);
if($_POST['group_choose'] == "exist")
{
	$group_id = $_POST["sCmsGroup"];
}elseif($_POST['group_choose'] == "new"){
	if(!empty($sCmsNewGroup))
	{
		$queryInsert = mysql_query("
		INSERT INTO s_cms_groups (description) VALUES ('{$_POST["sCmsNewGroup"]}')
		");
		$group_id = mysql_insert_id();
		
		//TODO
		//Tree aktualisieren
		?>
		<script type="text/javascript">
		parent.reloadTreeExtern();
		</script>
		<?php
	}
}


if ($_GET["delete"]){
	$delete = mysql_query("
	DELETE FROM s_cms_content WHERE id={$_GET["delete"]}
	");
	
	$sInform = $sLang["cms"]["cms_article_deleted"];
}



if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
	
	if (!$_POST["sCmsHeadline"]) $sError = $sLang["cms"]["cms_enter_title"];
	
	
	$_POST["sCmsDatum"] = makeProperDate($_POST["sCmsDatum"]);
	if ($_FILES["sCmsPicture"]["tmp_name"]){
		
		$pictureResult = upload("sCmsPicture",array("jpg","jpeg"),"../../../../images/cms/",150);
		if ($pictureResult=="WRONG FILE"){
			$sError = $sLang["cms"]["cms_only_jpeg_accepted"];
		}else {
			$picture = ", img='$pictureResult' ";
		}
	}
	if ($_FILES["sCmsFile"]["tmp_name"]){
		$fileResult = upload("sCmsFile","","../../../../files/cms/",0);
		if ($fileResult=="WRONG FILE"){
			$sError = $sLang["cms"]["cms_only_jpeg_accepted"];
		}else {
			$file = ", attachment='$fileResult' ";
		}
	}
	
	
	if (!$sError){
		$_POST["sCmsHeadline"] = mysql_real_escape_string($_POST["sCmsHeadline"]);
		$_POST["sCmsText"] = mysql_real_escape_string($_POST["sCmsText"]);
		$_POST["sCmsLink"] = mysql_real_escape_string($_POST["sCmsLink"]);
		
		if (!empty($_GET["edit"])){
			$insertArticle = mysql_query("
			UPDATE s_cms_content SET
			description='{$_POST["sCmsHeadline"]}',
			text='{$_POST["sCmsText"]}',
			link='{$_POST["sCmsLink"]}',
			datum='{$_POST["sCmsDatum"]}',
			groupID='{$group_id}'
			$picture
			$file
			WHERE id={$_GET["edit"]}
			");
		}else {
			$insertArticle = mysql_query("
			INSERT INTO s_cms_content
			(groupID, description, text, datum, img, link, attachment)
			VALUES (
			{$group_id},
			'{$_POST["sCmsHeadline"]}',
			'{$_POST["sCmsText"]}',
			'{$_POST["sCmsDatum"]}',
			'$pictureResult',
			'{$_POST["sCmsLink"]}',
			'$fileResult'
			)
			");
			$last_content_id = mysql_insert_id();
		}
		
		if ($insertArticle){
			$sInform = $sLang["cms"]["cms_Entry_was_saved"];
		}
		//Tree aktualiseren
		?>
		<script type="text/javascript">
		parent.reloadTreeExtern();
		</script>
		<?php
		
		//Nach der Speicherung den Bearbeitungsmodus starten
		if(empty($_GET["edit"]))
		{
			echo "<meta http-equiv='refresh' content='0; url=cms.php?edit=cms{$last_content_id}'>";
		}
	}
}

if (!empty($_GET["edit"])){
	$_GET["edit"] = preg_replace("/cms/","",$_GET["edit"]);
	$article = mysql_query("
	SELECT description,text,img,link,attachment,DATE_FORMAT(datum,'%d.%m.%Y') AS datum FROM s_cms_content WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($article)){
		$sInform = $sLang["cms"]["cms_article_not_found"];
	}else {
		$article = mysql_fetch_array($article);
		$_POST["sCmsHeadline"] = $article["description"];
		$_POST["sCmsText"] = $article["text"];
		$_POST["sCmsDatum"] = $article["datum"];
		$_POST["sCmsPicture"] = $article["img"];
		$_POST["sCmsFile"] = $article["attachment"];
		$_POST["sCmsLink"] = $article["link"];
		
		
		
	}
}
?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

</head>

<body >
<script language="javascript" type="text/javascript">
   	tinyMCE.init({
		// General options
		mode: "exact",
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
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $group_id?>&delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["cms"]["cms_should_the_article"] ?> "'+text+'" <?php echo $sLang["cms"]["cms_really_delete"] ?>',window,'deleteArticle',ev);
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

function validateFields()
{
	var error = false;
	var focus_id = "";
	
	//Reset
	$('sCmsNewGroup').setStyle('border', '1px solid #cccccc');
	$('sCmsHeadline').setStyle('border', '1px solid #cccccc');
	
	//Group Vali
	if($('radio_new').checked == true)
	{
		var newgroupname = $('sCmsNewGroup').value
		if(newgroupname.trim() == "")
		{
			$('sCmsNewGroup').setStyle('border', '1px solid red');
			parent.parent.Growl("<?php echo $sLang["cmsstatic"]["cms_enter_groupname"] ?>");
			$('sCmsNewGroup').value = "";
			if(focus_id == "") focus_id = "sCmsNewGroup";
			error = true;
		}
	}
	
	//Überschrift Validierung
	var headline = $('sCmsHeadline').value;
	if(headline.trim() == "")
	{
		$('sCmsHeadline').setStyle('border', '1px solid red');
		parent.parent.Growl("<?php echo $sLang["cmsstatic"]["cms_enter_title"] ?>");
		$('sCmsHeadline').value = "";
		if(focus_id == "") focus_id = "sCmsHeadline";
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

Ext.onReady(function(){
	//Safari display hack
	if(Ext.isSafari)
	{
		$('file_upload_pic').setStyle('height', '30');
		$('file_upload_pic').setStyle('width', '240');
		$('file_upload_data').setStyle('height', '30');
		$('file_upload_data').setStyle('width', '240');
	}
});

</script>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>&new=<?php echo $_GET["new"]?>">
<fieldset>
<legend><?php echo $sLang["cms"]["cms_choose_the_group"] ?></legend>
<ul>
<li>
		<input style="position:relative; top:-5px;" type="radio" name="group_choose" value="exist" checked="checked"/>
		<label style="width:90px; text-align:left;" for="name"><?php echo $sLang["cms"]["cms_group"] ?></label>
		
		
		<select name="sCmsGroup">
		<?php
		$getGroups = mysql_query("
		SELECT id, description FROM s_cms_groups ORDER BY id ASC
		");
		while ($group=mysql_fetch_array($getGroups)){
		?>
		<option value="<?php echo $group["id"] ?>" <?php echo $_POST["group"]==$group["id"] || $group_id==$group["id"] ? "selected" : ""?>><?php echo $group["description"]?></option>	
		<?php
		}
		?>
		</select>
		
		
		</li>
		<li>
		<input id="radio_new" style="position:relative; top:-5px;" type="radio" name="group_choose" value="new">
		<label style="width:90px; text-align:left;" for="name"><?php echo $sLang["cms"]["cms_or_new"] ?></label>
		<input name="sCmsNewGroup" type="text" id="sCmsNewGroup" class="w75" value="<?php echo $_POST["sBannerName"] ?>" />
		</li>
		
		<li class="clear"></li>
		
		<!--<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button  onClick="$('groupChange').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["cms"]["cms_update"] ?></div></button></li>	
			
		
		</ul>
		</div>-->
			<li class="clear"></li>
</ul>
</fieldset>

<?php
if ($_GET["edit"] || $_GET["new"]){
?>

		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $sLang["cms"]["cms_edit_content"] ?></legend>
		<ul>
		
		<li><label style="width:90px;" for="name"><?php echo $sLang["cms"]["cms_title"] ?></label><input id="sCmsHeadline" name="sCmsHeadline" type="text"  style="height:25px;width:250px" class="w200" value="<?php echo $_POST["sCmsHeadline"] ?>" /></li>
		<li class="clear"/>

		<li><label style="width:90px; text-align:left" for="name"><?php echo $sLang["cms"]["cms_text"] ?></label>
		</li>
		<li class="clear"></li>
		<li>
		<textarea name="sCmsText" type="text" id="txtlangbeschreibung"><?php echo $_POST["sCmsText"] ?></textarea></li>
		<li class="clear"/>
		
		<li><label style="width:90px; text-align:left" for="name"><?php echo $sLang["cms"]["cms_date"] ?></label>
		<input class="w75" id="sCmsDatum" name="sCmsDatum" value="<?php echo $_POST["sCmsDatum"]?>" onClick="displayDatePicker('sCmsDatum', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sCmsDatum', this, 'dmy', '.');"></a>
		</li>
		
	<li class="clear"/>
		
		<li><label style="width:90px; text-align:left" for="name"><?php echo $sLang["cms"]["cms_image_upload"] ?></label><input id="file_upload_pic" name="sCmsPicture" type="file"  class="w75" style="height:21px;" /></li><li class="clear"/>
		
		
		<?php
			if ($_POST["sCmsPicture"]){
				echo "<li><img src=\"../../../../images/cms/{$_POST["sCmsPicture"]}Thumb.jpg\"></li><li class=\"clear\"/>";
				echo "<li><a href='".basename(__FILE__)."?edit=cms".$_GET['edit']."&deleteImg=1'>[Bild entfernen]</a></li><li class=\"clear\"/>";
			}
		?>
		<!-- File-Upload -->
		<li><label style="width:90px; text-align:left" for="name"><?php echo $sLang["cms"]["cms_file_upload"] ?></label><input id="file_upload_data" name="sCmsFile" type="file"  class="w75" /></li><li class="clear"/>
		<?php
			if ($_POST["sCmsFile"]){
				echo "<li><a href=\"../../../../files/cms/{$_POST["sCmsFile"]}\" target=\"_blank\">Datei herunterladen</a></li><li class=\"clear\"/>";
				echo "<li><a href='".basename(__FILE__)."?edit=cms".$_GET['edit']."&deleteAttachment=1'>[Datei entfernen]</a></li><li class=\"clear\"/>";
			}
		?>
		
		<li><label style="width:90px; text-align:left" for="name"><?php echo $sLang["cms"]["cms_extern_link"] ?></label><input name="sCmsLink" style="height:25px;width:250px" type="text"  class="w200" value="<?php echo $_POST["sCmsLink"] ?>" /></li>
		
		<?php
		if(empty($_GET["edit"]))
		{
			$btn_text = $sLang["cms"]["cms_save"];
		}else{
			$btn_text = $sLang["cms"]["cms_save_edit"];
		}
		?>
			
		<li class="clear"></li>
		
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="return validateFields();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $btn_text ?></div></button></li>	
			
		
		</ul>
		</div>
		
		
		</ul>
		</fieldset>
		</form>
<?php
}
?>
		
		

</body>

</html><?php die(); ?>