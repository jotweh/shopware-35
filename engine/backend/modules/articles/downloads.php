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

$sCore->sInitConfig();
if (!$_GET["article"]) die($sLang["articles"]["downloads_no_article"]);


function upload(){
	$filename = $_FILES['upload']['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = $filename[count($filename)-1];
	if (in_array($filenameext,array("php","php5","php4","phtml","cgi","pl"))){
		//echo $filenameext;
		die("Upload of $filenameext files is forbidden");
	}
	$filename = $filename[0];
	$filename = str_replace(" ","",$filename);
	// Random-Part for permit overwrite of existing files
	// (Article-ID | Download-Id)
	$filename = $filename."-".$_GET["article"].rand(0,10000).".".$filenameext;
	
		
	if (move_uploaded_file($_FILES['upload']['tmp_name'], "../../../../files/downloads/".$filename)){
		return $filename;
	}else {
		return "ERROR";
	}
	
	
}

if ($_POST["saveNow"]){
	// Alle Bedingungen erfüllt??
	if (!$_POST["description"]){
		$fehler = 1;
	}
	
	if (!$fehler){
		// Falls Datei Upload
		
		if ($_GET["edit"]){
			// Editieren
			if ($_FILES["upload"]["tmp_name"]){
				$filename = upload();
				if ($filename!="ERROR"){
					
					// Alten Download entfernen
					$queryOldDownload = mysql_query("
					SELECT filename FROM s_articles_downloads WHERE id={$_GET["edit"]}
					");
					
					if (@mysql_num_rows($queryOldDownload)){
						unlink("../../../../files/downloads/".mysql_result($queryOldDownload,0,"filename"));
					}
					// Aktualisieren
					$insertLink = mysql_query("
					UPDATE s_articles_downloads SET description='{$_POST["description"]}',
					filename='$filename',size={$_FILES["upload"]["size"]}					
					WHERE id={$_GET["edit"]}
					");
				}else {
					$sError = $sLang["articles"]["downloads_upload_failed"];
				}
			}else {
				$insertLink = mysql_query("
				UPDATE s_articles_downloads SET description='{$_POST["description"]}' WHERE id={$_GET["edit"]}
				");
			}
			#$updateLink = mysql_query("
			#UPDATE s_articles_information SET description='{$_POST["description"]}', link='{$_POST["link"]}', target='{$_POST["target"]}'
			#WHERE id={$_GET["edit"]}
			#");
			
		}else {
			//error_reporting(E_ALL);
			if (!$_FILES["upload"]["tmp_name"]){
				$sError = $sLang["articles"]["downloads_select_file"];
			}else {
				$filename = upload();
				if ($filename!="ERROR"){
					// Hinzufügen
					$insertLink = mysql_query("
					INSERT INTO s_articles_downloads (articleID, description, filename, size)
					VALUES ({$_GET["article"]},'{$_POST["description"]}','$filename',{$_FILES["upload"]["size"]})
					");
				}else {
					$sError = $sLang["articles"]["downloads_upload_failed"];
				}
			}
			
		}	
		
		if ($insertLink || $updateLink){
			$sInform = $sLang["articles"]["downloads_download_successfully"];
		}else {
			$sError = $sLang["articles"]["downloads_add_download_failed"];
		}
		
	}else {
			$sError = $sLang["articles"]["downloads_enter_title"];
	}
}
// ===========================================
// Delete?
if ($_GET["delete"]){
	// Alten Download entfernen
	$queryOldDownload = mysql_query("
	SELECT filename FROM s_articles_downloads WHERE id={$_GET["delete"]}
	");
	
	if (@mysql_num_rows($queryOldDownload)){
		$filename = "../../../../files/downloads/".mysql_result($queryOldDownload,0,"filename");
		
		unlink($filename);
	}
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_downloads WHERE id=".$_GET["delete"]."
	");
	
	if ($abfrage){
		$sInform = $sLang["articles"]["downloads_download_deleted"];
	}else {
		$sError = $sLang["articles"]["downloads_delete_download_failed"]."<br>".mysql_error();
	}
}

// Edit?
if ($_GET["edit"]){
	$abfrage = mysql_query("
	SELECT * FROM s_articles_downloads WHERE id=".$_GET["edit"]."
	");
	
	$editArray = mysql_fetch_array($abfrage);
	
	$_POST["description"] = $editArray["description"];
	$_POST["filename"] = $editArray["filename"];
	$_POST["size"] = $editArray["size"];
	// Size in MB
	$_POST["size"] = round ($_POST["size"]/1024/1024,2);
	
}

if(!$sInform) $sInform = "";
if (!$sError) $sError = "";


$sCore->sInitTranslations($_GET["edit"],"download");

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

<title><?php echo $sLang["articles"]["downloads_links"] ?></title>

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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["downloads_the_download"] ?> "'+text+'" <?php echo $sLang["articles"]["downloads_really_delete"] ?>',window,'deleteLink',ev);
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

if (isset($_GET["edit"]) || isset($_GET["newLink"])){
?>
<fieldset style="margin-top:-20px; margin-bottom: 0px;">
	<legend><a class="ico attach"></a> <?php if (!$_GET["edit"]) { echo $sLang["articles"]["downloads_new_download"]; } else { echo $sLang["articles"]["downloads_edit_download"]; } ?></legend>
	<form enctype="multipart/form-data" id="save" name="save" method="post" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&edit=".$_GET["edit"]?>" onsubmit="">
  	<input type="hidden" name="saveNow" value="1">
		
	<ul>
	<li><label for="name"><?php echo $sLang["articles"]["downloads_title"] ?></label>
	<input name="description" type="text" id="downloadname" class="w200" value="<?php echo $_POST["description"]; ?>" /><?php echo $sCore->sBuildTranslation("downloadname","downloadname",$_GET["edit"],"download"); ?>
	</li>
	<li class="clear"/>
	</ul>
	 
	<ul>
	<li>
	<label for="img"><?php echo $sLang["articles"]["downloads_file_upload"] ?></label>
	<input type="file" class="w200" name="upload">
	</li>
	<li class="clear"></li>
	</ul>
	<?php
	if ($_POST["filename"]){
	?>
	
	
	<br />
	<div style="background-color:#e9f5fb; width:300px;height:65px; float:right; padding:10px; border:1px solid #59b8e6;">
	<strong><?php echo $sLang["articles"]["downloads_information"]?></strong> <br />
	<?php echo $sLang["articles"]["downloads_filesize"] ?> <?php echo $_POST["size"] ?> <?php echo $sLang["articles"]["downloads_Megabyte"] ?><br />
	<a class="ico2 disc" style="cursor:pointer;width:200px;background-position: 0 1px;" href="http://<?php echo $sCore->sCONFIG["sBASEPATH"]."/files/downloads/".$_POST["filename"]?>" target="_blank"><?php echo $sLang["articles"]["downloads_download_files"] ?></a>
	</div>
	<?php
	}
	?>
	
	
<div class="buttons" id="div" style="margin-top:10px; text-align:left;">
<ul style="text-align:left;">
<li id="buttonTemplate" class="buttonTemplate" style="text-align:left; float:left;">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["downloads_save"] ?></div>
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
<legend style="font-weight:bold;"><a class="ico attach"></a> <?php echo $sLang["articles"]["downloads_new_Attachment"] ?></legend>
<?php echo $sLang["articles"]["downloads_add_a_download"] ?>
<form method="post" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&newLink=1"?>">
<div class="buttons" id="div" style="margin-top:10px; text-align:left;">
<ul style="text-align:left;">
<li id="buttonTemplate" class="buttonTemplate" style="text-align:left; float:left;">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["downloads_new_download"] ?></div>
</button>
</li>
</ul>
</div>
</form>
</fieldset>

<?php
$sql = 
 "
 SELECT * FROM s_articles_downloads WHERE articleID={$_GET["article"]} ORDER BY description ASC
 ";


 $getLinks = mysql_query($sql);
 
 ?>
 
<?php
$numberLinks = mysql_num_rows($getLinks);

if ($numberLinks){
?>
<fieldset class="grey" style="margin-top:0px; padding:0 0 0 0;">
 <table width="100%"  border="0" cellpadding="2" cellspacing="1" bordercolor="#CCCCCC">
 
   <tr style="height:22px;">
         <td width="50%" nowrap="nowrap" class="th_bold"><?php echo $sLang["articles"]["downloads_info"] ?></td>     
         <td  width="50%" class="th_bold"><?php echo $sLang["articles"]["downloads_options"] ?></td>
   </tr>
  
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
     <td  width="50%" nowrap="nowrap"><?php echo $link["description"] ?></td>
         
     <td  width="50%"><a class="ico delete" style="cursor:pointer" onclick="deleteLink(<?php echo $link["id"] ?>,'<?php echo $link["description"] ?>')"></a><a class="ico pencil" style="cursor:pointer" onclick="window.location='?article=<?php echo $_GET["article"] ?>&edit=<?php echo $link["id"]?>'"></a><a class="ico disc" style="cursor:pointer" target="_blank" href="http://<?php echo $sCore->sCONFIG["sBASEPATH"]."/files/downloads/".$link["filename"]?>"></a></td>
	</tr>
	
	<?php
// =================================
} // for every supplier
// =================================
?>
</table></fieldset>
<?php
// =================================
}
?>
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>