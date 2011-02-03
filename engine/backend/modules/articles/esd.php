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

if (!$_GET["article"]) die($sLang["articles"]["esd_no_article"]);

if ($_POST["saveNow"]){
	// Save ESD-Relation
	
	if (!$_POST["sEsdSerials"]) $_POST["sEsdSerials"] = "0";
	if (!$_POST["sEsdMail"]) $_POST["sEsdMail"] = "0";
	if (!$_POST["sEsdMax"]) $_POST["sEsdMax"] = "0";

	if (!$_GET["edit"]){
		$sql = "
		INSERT INTO s_articles_esd
		(articleID, articledetailsID, file, serials, notification, maxdownloads, datum)
		VALUES (
		{$_GET["article"]},
		{$_POST["sEsdArticle"]},
		'{$_POST["sEsdFiles"]}',
		{$_POST["sEsdSerials"]},
		{$_POST["sEsdMail"]},
		{$_POST["sEsdMax"]},
		now()
		)
		";

	}else {
		$sql = "
		UPDATE s_articles_esd
		SET articledetailsID={$_POST["sEsdArticle"]},
		serials={$_POST["sEsdSerials"]},
		notification={$_POST["sEsdMail"]},
		maxdownloads={$_POST["sEsdMax"]}
		WHERE id={$_GET["edit"]}
		";
		$esdID = $_GET["edit"];
	}
	
	
	
	$insert = mysql_query($sql);
	if (!$_GET["edit"]){
		$esdID = mysql_insert_id();
		header("location: ".$_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&edit=".$esdID);
	}
	
	if (!$insert){
		$sError = $sLang["articles"]["esd_Creating_esd_failed"];	
	}else {
		$sInform = $sLang["articles"]["esd_Creating_esd_successfull"];
	}
	
	// Einfügen der Seriennummern

	if ($_POST["sEsdSerials"] && $_POST["sEsdImport"]){
	
		$serialnumbers = preg_split("/\n|\r/", $_POST["sEsdImport"], -1, PREG_SPLIT_NO_EMPTY);

		if (count($serialnumbers)){
			foreach ($serialnumbers as $serialnumber){
				if ($serialnumber && strlen($serialnumber)>4){
					// Import serial
					// Don´t duplicate serials
					$serialnumber = trim($serialnumber);
	
						// Insert serial
						$sql = "
						INSERT INTO s_articles_esd_serials
						(serialnumber, esdID)
						VALUES ('$serialnumber',{$_GET["edit"]})
						";
						//echo $sql;
						$insertSerial = mysql_query($sql);
						//echo mysql_error();
						$i++;
					
				}
			}
			if (empty($i)) $i = "0";
			echo "<a class=\"ico information\"></a>".$i." ".$sLang["articles"]["esd_new_serial_numbers_imported"];
		}
	}
	
}

if ($_POST["save"]){
	
	if(!empty($_POST["sEsdFile2"]))
		$_POST["sEsdFile"] = $_POST["sEsdFile2"];
	$file = mysql_real_escape_string($_POST["sEsdFile"]);
	$sql = "
			UPDATE s_articles_esd
			SET file='$file'
			WHERE id={$_GET["edit"]}
	";
	 mysql_query($sql);
}

// ===========================================
// Delete?
if ($_GET["delete"]){
	$abfrage = mysql_query("
	DELETE FROM s_articles_esd WHERE id=".$_GET["delete"]."
	");
	
	if ($abfrage){
		$sInform = $sLang["articles"]["esd_deleting_esd"];
	}else {
		$sError = $sLang["articles"]["esd_deleting_esd_failed"]."<br>".mysql_error();
	}
}

// Edit?
if ($_GET["edit"]){
	$abfrage = mysql_query("
	SELECT * FROM s_articles_esd WHERE id=".$_GET["edit"]."
	");
	
	$editArray = mysql_fetch_array($abfrage);
	
	$_POST["sEsdArticle"] = $editArray["articledetailsID"];
	
	$_POST["sEsdFiles"] = $editArray["file"];
	$_POST["sEsdSerials"] = $editArray["serials"];
	$_POST["sEsdMail"] = $editArray["notification"];
	$_POST["sEsdMax"] = $editArray["maxdownloads"];
	

}

if(!$sInform) $sInform = "";
if (!$sError) $sError = "";

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

<title><?php echo $sLang["articles"]["esd_link"] ?></title>
<script type="text/javascript" src="../../../backend/js/moo12-core.js"></script>
<script type="text/javascript" src="../../../backend/js/moo12-more.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../vendor/swfupload/css/default.css" rel="stylesheet" type="text/css" />


<script type="text/javascript" src="../../../vendor/swfupload/source/swfupload.js"></script>
<script type="text/javascript" src="uploadesd.js"></script>

</head>
<body>




<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteEsd":
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

function deleteEsd(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["esd_the_ESD_version"] ?> "'+text+'" <?php echo $sLang["articles"]["esd_really_detele"] ?>',window,'deleteEsd',ev);
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

function toggle(){
	if ($('serialField').getStyle('display')=="block"){
		$('serialField').setStyle('display','none');
	}else {
		$('serialField').setStyle('display','block');
	}
}
</script>
<?php if(!empty($_GET["edit"])) { ?>
<script type="text/javascript">

var upload1, upload2;

window.onload = function() {
			
			upload1 = new SWFUpload({
				// Backend Settings
				upload_url: "../../../backend/modules/articles/uploadEsd.php?esd=<?php echo $_GET["edit"]?>&article=<?php echo $_GET["article"] ?>&sUsername=<?php echo $_SESSION["sUsername"] ?>&sPassword=<?php echo $_SESSION["sPassword"]?>&sSession=<?php echo session_id()?>",	// Relative to the SWF file (or you can use absolute paths)
				// File Upload Settings
				file_size_limit : "102400",	// 100MB
				file_types : "*.*",
				file_types_description : "*.*",
				file_upload_limit : "10",
				file_queue_limit : "0",
				
				// Event Handler Settings (all my handlers are in the Handler.js file)
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				button_placeholder_id : "spanButtonPlaceholder",
				button_width: 150,
				button_image_url: "../../../backend/img/default/window/bg_bt_end.gif",
				button_height: 27,
				button_text : '<span class="button">Datei auswählen</span>',
				button_text_style : '.button { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }',
				button_text_top_padding: 2,
				button_text_left_padding: 15,
				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND,

				// Flash Settings
				flash_url : "../../../vendor/swfupload/source/swfupload.swf",	// Relative to this file (or you can use absolute paths)
				custom_settings : {
					upload_target : "divFileProgressContainer"
				},

				// UI Settings
				swfupload_element_id : "swfu_container",
				

				// Debug Settings
				debug: false
			});
		
			
}
</script>
<?php } ?>
<fieldset class="col2_cat2" style="margin-top:10px">
<legend><a class="ico help"></a><?php echo $sLang["articles"]["esd_shopware_esd-Module"] ?></legend>
<?php echo $sLang["articles"]["esd_sell_digital_products"]?>
</fieldset>

<?php
if ($_GET["edit"] || $_GET["newEsd"]){
?>


	
<?php
if (!$_GET["edit"]){
		// Read mainarticle and all variants
		$queryArticles = mysql_query("
		SELECT s_articles_details.id AS id,name, additionaltext
		FROM s_articles_details
		LEFT JOIN 
			s_articles_esd ON s_articles_esd.articledetailsID=s_articles_details.id
		LEFT JOIN
			s_articles ON s_articles_details.articleID=s_articles.id
		WHERE 
			s_articles_esd.articledetailsID IS NULL AND s_articles_details.articleID = {$_GET["article"]}
		");
  	}else {
  		$queryArticles = mysql_query("
		SELECT s_articles_details.id AS id,name, additionaltext
		FROM s_articles_details
		LEFT JOIN 
			s_articles_esd ON s_articles_esd.articledetailsID=s_articles_details.id
		LEFT JOIN
			s_articles ON s_articles_details.articleID=s_articles.id
		WHERE 
			s_articles_details.articleID = {$_GET["article"]}
		");
  	}
?>
<?php
if (!@mysql_num_rows($queryArticles)){
?>
<fieldset class="col2_cat2">
<legend><a class="ico exclamation"></a><span style=\"color:#F00\"><?php echo $sLang["articles"]["esd_no_more_esd-Modules"] ?></span></legend>
<strong><?php echo $sLang["articles"]["esd_already_all_variants_Article_ESD_data_assigned"] ?></strong><br /><br />
</fieldset>


	
<?php
} else {
?>
<div style="float: left;">
<form id="save" name="save" method="post" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&edit=".$_GET["edit"]?>" onsubmit="" enctype="multipart/form-data">

	<fieldset style="width:600px;float:left">
	<legend><?php echo $sLang["articles"]["esd_create_ESD_version"] ?></legend>

  	<input type="hidden" name="saveNow" value="1">

		<ul>
		<li><label for="name"><?php echo $sLang["articles"]["esd_Choice_article"] ?></label>
		<select id="sEsdArticle" name="sEsdArticle" class="w200" style="width:350px">
		<?php
			while ($esdArticle=mysql_fetch_array($queryArticles)){
			?>
				<option value="<?php echo $esdArticle["id"] ?>" <?php echo $_POST["sEsdArticle"] == $esdArticle["id"] ? "selected" : ""?>><?php echo $esdArticle["name"]."-".$esdArticle["additionaltext"] ?></option>
			<?php
			}
		?>
		</select>
		</li>
		<li class="clear"/>
		</ul>
	<?php
	if (!$_GET["edit"]){
	?>
	<div class="buttons" id="buttons" style="float:right;">
		<ul style="margin:0px">
			<li id="buttonTemplate" style="margin:0px" class="buttonTemplate">
			<button id="btnBrowse" style="margin:0px" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button>
			</li>	
		</ul>
	</div>
	<?php
	}
	?>	
<?php
}
?>	

<?php
if ($_GET["edit"]){
?>
<!-- From here -->
	<ul>
	<li>
	
	</li>
	<li class="clear"/>
	</ul>
	<!-- // To here -->
	
	<ul>
	<li><label for="name"><?php echo $sLang["articles"]["esd_manage_serialnumbers"] ?></label>
	<input type="checkbox" name="sEsdSerials" value=1 onclick="toggle()" <?php echo $_POST["sEsdSerials"] || !count($_POST) ? "checked" : ""?>>
	</li>
	<li class="clear"/>
	</ul>
	
	<!-- If Edit -->
	<ul id="serialField" <?php if (count($_POST) && !$_POST["sEsdSerials"]) echo "style=\"display:none\"";?>>
	<li ><label for="name"><?php echo $sLang["articles"]["esd_new_serialnumber"] ?></label>
	<textarea cols=40 rows=10 name="sEsdImport"></textarea>
	</li>
	<li class="clear"/>
	</ul>
	
	<div class="buttons" id="buttons" style="float:right;">
		<ul style="margin:0px">
			<li id="buttonTemplate" style="margin:0px" class="buttonTemplate">
			<button id="btnBrowse" style="margin:0px" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button>
			</li>	
		</ul>
	</div>
	
	</fieldset>
	</form>
	
	<form action="upload.php" method="post" enctype="multipart/form-data">
	<fieldset style="width:600px;float:left">
	<legend>Datei hochladen</legend>
	<div style="float:right;background: transparent url(../../../backend/img/default/window/bg_bt.gif) no-repeat scroll 0 0; padding: 0 0 0 10;width: 150px">
				<span id="spanButtonPlaceholder"></span>
	</div>
	
	<div id="divFileProgressContainer" style=""></div>
	</div>
	</fieldset>
	</form>
<?php
} // This fields only in edit-mode
?>
</div>
	<?php if ($_GET["edit"] ){
	?>
		
		<fieldset style="float:left;width:300px;">
		<legend><?php echo $sLang["articles"]["esd_information"] ?></legend> <br />
		<?php
		if ($_POST["sEsdSerials"]){
			$checkAssignedSerials = mysql_query("
			SELECT s_articles_esd_serials.id AS id FROM s_articles_esd_serials, s_order_esd
			WHERE s_order_esd.esdID={$_GET["edit"]}
			AND s_order_esd.serialID=s_articles_esd_serials.id
			AND s_order_esd.esdID=s_articles_esd_serials.esdID
			AND s_order_esd.userID!=0
			");
			$numberAssignedSerials = @mysql_num_rows($checkAssignedSerials);
		?>
		<?php echo $sLang["articles"]["esd_available_serialnumbers"]?> <?php
	
		$checkSerial = mysql_query("
		SELECT s_articles_esd_serials.id AS id FROM s_articles_esd_serials
		WHERE s_articles_esd_serials.esdID={$_GET["edit"]}
		");
		$numberAvailableSerials = @mysql_num_rows($checkSerial);
		if ($numberAvailableSerials > 0){
			echo ($numberAvailableSerials-$numberAssignedSerials);
		}else {
			echo "0";
		}
		?><br />
		<?php echo $sLang["articles"]["esd_Subcontracted_serialnumbers"] ?> <?php
		
		echo $numberAssignedSerials ? $numberAssignedSerials : 0;
		?><br /><br />
		<?php
		}
		if ($_POST["sEsdFiles"]){
		?>
		<a class="ico disk" style="cursor:pointer" href="http://<?php echo $sCore->sCONFIG["sBASEPATH"]."/files/".$sCore->sCONFIG["sESDKEY"]."/".$_POST["sEsdFiles"]?>" target="_blank"></a><?php echo $sLang["articles"]["esd_download_file"] ?>
		<?php
		}
		?>	
		</fieldset>


<?php
	$uploaddir = '../../../../files/'.$sCore->sCONFIG['sESDKEY'].'/';
	$files = array();
	foreach (glob($uploaddir."*.*") as $file)
		$files[] = htmlentities(basename($file),ENT_QUOTES);
		
	if(!empty($_POST["sEsdFiles"]))
		$selected_file = htmlentities($_POST["sEsdFiles"],ENT_QUOTES);
	else 
		$selected_file = '';
?>
	<form action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&edit=".$_GET["edit"]?>" method="post">
	<input type="hidden" name="save" value="file">
	<fieldset style="float:left;width:300px;">
		<legend>Datei Auswahl:</legend> <br />
		<ul>
		<li><label for="name" style="width:90px">Datei:</label>
			<select name="sEsdFile" class="w200" style="width:180px">
				<?php if(!empty($files)) {?>
				<option value="">bitte w&auml;hlen</option>
				<?php } else {?>
				<option value="">keine Vorhanden</option>
				<?php }?>
				<?php foreach ($files as $file) {?>
					<option <?php if($file==$selected_file) echo "selected";?>><?php echo$file?></option>
				<?php }?>
				<?php if(!in_array($selected_file, $files)) {?>
					<option selected><?php echo$selected_file?> (noch nicht vorhanden)</option>
				<?php }?>
			</select>
		</li>
		<li><label for="name" style="width:90px">oder Eingabe:</label>
			<input type="text" name="sEsdFile2" value="">
		</li>
		<li class="clear"/>
		<li>
		<div class="buttons" id="buttons" style="float:right;">
			<ul style="margin:0px">
				<li id="buttonTemplate" style="margin:0px" class="buttonTemplate">
				<button id="btnBrowse" style="margin:0px" type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button>
				</li>	
			</ul>
		</div>
		</li>
		</ul>
	</fieldset>
	</form>

	<?php
	}
	?>

<div style="clear:both"></div>
<?php

}else { // Edit, create mask
	if (!$licenceFailed){
	?>
		<div class="buttons" id="buttons">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate" style="float:right;margin-left:10px;">
			<div class="button"><a href="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]."&newEsd=1"?>"><div class="buttonLabel"><?php echo $sLang["articles"]["esd_new_esd-version_added"] ?></div></a></div>
			</li>	
			</ul>
		</div> 
		<div class="clear"></div>
	<?php
	}
}
?>


<br /><br /><br />
<?php
$sql = "
		SELECT s_articles_details.id AS detailsID, s_articles_esd.id AS id, file,serials, name, additionaltext
		FROM s_articles, s_articles_esd, s_articles_details
		WHERE
		s_articles.id={$_GET["article"]} AND
		s_articles_details.articleID=s_articles.id AND
		s_articles_details.id=s_articles_esd.articledetailsID
		AND s_articles.id=s_articles_esd.articleID
		";
		
		$queryEsd = mysql_query($sql);
	if (@mysql_num_rows($queryEsd)){
?>
<fieldset class="col2_cat2">
<legend><?php echo $sLang["articles"]["esd_Already-based versions"] ?></legend>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   <?php
		// Get all related esd-versions
		
		while ($esd=mysql_fetch_array($queryEsd)){
   ?>
     <tr class="rowcolor2">
       <th><a class="ico folder"></a><?php echo $esd["name"]."-".$esd["additionaltext"] ?></th>
       <td class="last-child">
       <?php
		if ($esd["serials"]){
		// Main-window to edit serials
       ?>
       	   <a class="ico key" style="cursor:pointer" onclick="parent.parent.loadSkeleton('search_esd',false, {article:<?php echo $esd["id"] ?>})"></a>
       <?php
		}
       ?>
		   <a class="ico delete" style="cursor:pointer" onclick="deleteEsd(<?php echo $esd["id"] ?>,'<?php echo $esd["name"]."-".$esd["additionaltext"] ?>')"></a>
		   <a class="ico pencil" style="cursor:pointer" href="<?php echo $_SERVER["PHP_SELF"]."?edit=".$esd["id"]?>&article=<?php echo $_GET["article"]?>"></a>
	  </td>
     </tr>
     <?php
		}
     ?>
    
     
 </table>
</fieldset>
<?php
	}
?>




</body>
</html>