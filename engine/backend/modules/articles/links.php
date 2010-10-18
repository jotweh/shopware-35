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
	parent.parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
// *****************
?>
<?php
if (!$_GET["article"]) die("No Article");

// *****************
// SPEICHERN DER HERSTELLER
// ===========================================
if ($_POST["saveNow"]){
	// Alle Bedingungen erfüllt??
	if (!$_POST["description"] || !$_POST["link"]){
		$fehler = 1;
	}
	
	if (!$fehler){
		// Falls Datei Upload
		
		if ($_GET["edit"]){
			// Editieren
			$updateLink = mysql_query("
			UPDATE s_articles_information SET description='{$_POST["description"]}', link='{$_POST["link"]}', target='{$_POST["target"]}'
			WHERE id={$_GET["edit"]}
			");
			
		}else {
			// Hinzufügen
			$insertLink = mysql_query("
			INSERT INTO s_articles_information (articleID, description, link, target)
			VALUES ({$_GET["article"]},'{$_POST["description"]}','{$_POST["link"]}','{$_POST["target"]}')
			");
		}	
		
		if ($insertLink || $updateLink){
			$sInform = $sLang["articles"]["links_link_saved"];
		}else {
			$sError = $sLang["articles"]["links_link_save_failed"]; die(mysql_error());
		}
		
	}else {
			$sError = $sLang["articles"]["links_enter_title_and_link"];
	}
}
// ===========================================
// Delete?
if ($_GET["delete"]){
	$abfrage = mysql_query("
	DELETE FROM s_articles_information WHERE id=".$_GET["delete"]."
	");
	
	if ($abfrage){
		$sInform = $sLang["articles"]["links_link_deleted"];
	}else {
		$sError = $sLang["articles"]["links_cant_Delete_link"]."<br>".mysql_error();
	}
}

// Edit?
if ($_GET["edit"]){
	$abfrage = mysql_query("
	SELECT * FROM s_articles_information WHERE id=".$_GET["edit"]."
	");
	
	$editArray = mysql_fetch_array($abfrage);
	
	$_POST["description"] = $editArray["description"];
	$_POST["link"] = $editArray["link"];
	$_POST["target"] = $editArray["target"] ? $editArray["target"] : "_blank";
	$file = $editArray["img"];
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="Hamann-Media GmbH" />
<meta name="copyright" content="2007, Hamann-Media GmbH" />
<meta name="company" content="Hamann-Media GmbH - eBusiness-Spezialist aus dem Muensterland" />
<meta name="reply-to" content="info@hamann-media.de" />
<meta name="rating" content="general" />
<meta http-equiv="content-language" content="de" />

<title><?php echo $sLang["articles"]["links_link"] ?></title>

</head>
<body>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteLink":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?article=<?php echo $_GET["article"]?>&delete="+sId;
			break;
		case "newLink":
		window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?new=1";
			break;
		case "saveLink":
		try {
			$('save').submit();
		}catch (e) {} 
		
		break;
	}
}

function deleteLink(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["links_should_the_link"] ?> "'+text+'" <?php echo $sLang["articles"]["links_really_be_deleted"] ?>',window,'deleteLink',ev);
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
$sCore->sInitTranslations($_GET["edit"],"link");
?>


<?php
if ($_GET["edit"] || $_GET["newLink"]){
?>
<fieldset style="margin-top: -20px; margin-bottom: 0px;">
	<legend><a class="ico world_link"></a><?php if (!$_GET["edit"]) { echo $sLang["articles"]["links_new_link"]; } else { echo $sLang["articles"]["links_edit_link"]; } ?></legend>
	<form id="save" name="save" method="post" action="<?php echo $PHP_SELF."?article=".$_GET["article"]."&edit=".$_GET["edit"]?>" onsubmit="">
  	<input type="hidden" name="saveNow" value="1">

	<ul>
	<li><label for="name"><?php echo $sLang["articles"]["links_link_title"] ?></label>
	<?php
	
	?>
	<input name="description" type="text" id="linkname" class="w200" value="<?php echo $_POST["description"]; ?>" /><?php echo $sCore->sBuildTranslation("linkname","linkname",$_GET["edit"],"link"); ?>
	
	</li>
	<li class="clear"/>
	</ul>
	 
	<ul>
	<li>
	<label for="img"><?php echo $sLang["articles"]["links_link_URL"] ?></label>
	<input name="link" type="text" id="txtName" class="w200" value="<?php echo $_POST["link"]; ?>" />
	</li>
	<li class="clear"></li>
	</ul>
	
	<ul>
	<li>
	<label for="img"><?php echo $sLang["articles"]["links_link_target"] ?></label>
	<select class="w200" name="target">
	<option value="_parent" <?php if ($_POST["target"]=="_parent") echo "selected"; ?>><?php echo $sLang["articles"]["links_shopware"] ?></option>
	<option value="_blank" <?php if ($_POST["target"]=="_blank") echo "selected"; ?>><?php echo $sLang["articles"]["links_extern"] ?></option>
	</select>
	</li>
	<li class="clear"></li>
	</ul>
	<div class="buttons" id="div" style="margin-top:10px; text-align:left;">
	<ul style="text-align:left;">
<li id="buttonTemplate" class="buttonTemplate" style="text-align:left; float:left;">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["links_save"] ?></div>
</button>
</li>
</ul>
</div>
</form>
</fieldset><br />
<?php
}
?>
<fieldset style="margin-top: -20px;">
<legend style="font-weight:bold;"><a class="ico world_link"></a> <?php echo $sLang["articles"]["links_new_link"] ?></legend>
<?php echo $sLang["articles"]["links_Add_optional"] ?>
<form method="post" action="<?php echo $PHP_SELF."?article=".$_GET["article"]."&newLink=1"?>">
<div class="buttons" id="div" style="margin-top:10px; text-align:left;">
<ul style="text-align:left;">
<li id="buttonTemplate" class="buttonTemplate" style="text-align:left; float:left;">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["links_new_link"] ?></div>
</button>
</li>
</ul>
</div>
</form>
</fieldset>

<?php
$sql = 
 "
 SELECT * FROM s_articles_information WHERE articleID={$_GET["article"]} ORDER BY description ASC
 ";


 $getLinks = mysql_query($sql);
 
 ?>
 
<?php
$numberLinks = mysql_num_rows($getLinks);

if ($numberLinks){
?>
<fieldset class="grey" style="margin-top: 0px; padding:0 0 0 0;">
 <table width="100%"  border="0" cellpadding="2" cellspacing="1" bordercolor="#CCCCCC">
 
   <tr style="height:22px;">
         <td width="30%" nowrap="nowrap" class="th_bold"><?php echo $sLang["articles"]["links_info"] ?></td>
         <td  width="30%" class="th_bold"><?php echo $sLang["articles"]["links_link_1"] ?></td>          
         <td  width="30%" class="th_bold"><?php echo $sLang["articles"]["links_options"] ?></td>
   </tr
  
<?php
// =================================
	
// =================================
// Ausgabe suppliers
// =================================
while ($link=mysql_fetch_array($getLinks))
{
	$i++;
	$comma = $i==$numberLinks ? "" : ",";
	?>
	<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:40px;border-bottom: 1px solid #a0a0a0;">
     <td  width="30%" nowrap="nowrap"><?php echo $link["description"] ?></td>
     <td  width="30%"><?php echo $link['link'] ?></td>          
     <td  width="30%"><a class="ico delete" style="cursor:pointer" onclick="deleteLink(<?php echo $link["id"] ?>,'<?php echo $link["description"] ?>')"></a><a class="ico pencil" style="cursor:pointer" onclick="window.location='?article=<?php echo $_GET["article"] ?>&edit=<?php echo $link["id"]?>'"></a></td>
	</tr>
		<?php
// =================================
} // for every supplier
// =================================
?>
</table></fieldset>
<?php
// =================================
} // Suppliers found 

// =================================
?>
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>