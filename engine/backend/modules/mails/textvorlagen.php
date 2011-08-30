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







if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
		
	if (!$sError){
		if ($_GET["edit"]){
			
			$_POST["frommail"] = mysql_real_escape_string($_POST["frommail"]);
			$_POST["fromname"] = mysql_real_escape_string($_POST["fromname"]);
			$_POST["subject"] = mysql_real_escape_string($_POST["subject"]);
			$_POST["content"] = mysql_real_escape_string($_POST["content"]);
			$_POST["ishtml"] = mysql_real_escape_string($_POST["ishtml"]);
			$_POST["attachment"] = mysql_real_escape_string($_POST["attachment"]);
			//die($_POST["contentHTML"]);
			$_POST["contentHTML"] = mysql_real_escape_string($_POST["contentHTML"]);
			
			$sql = "
			UPDATE s_core_config_mails
			SET
			frommail='{$_POST["frommail"]}',
			fromname='{$_POST["fromname"]}',
			subject='{$_POST["subject"]}',
			content='{$_POST["content"]}',
			contentHTML='{$_POST["contentHTML"]}',
			ishtml = '{$_POST["ishtml"]}',
			attachment = '{$_POST["attachment"]}'
			WHERE id={$_GET["edit"]}
			";
			$insertArticle = mysql_query($sql);
			
			
		}
		
		if ($insertArticle){
			$sInform = $sLang["mails"]["textvorlagen_entry_saved"];
		}
	}
}

if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_core_config_mails WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["mails"]["textvorlagen_mail_not_found"];
	}else {		
		$getSite = mysql_fetch_array($getSite);
		$sCore->sInitTranslations($_GET["edit"],"mails");
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["mails"]["textvorlagen_sould_the_site"] ?> "'+text+'" <?php echo $sLang["mails"]["textvorlagen_really_be_deleted"] ?>',window,'deleteArticle',ev);
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
$sCore->sInitTranslations(1,"config_mails","true");
?>

<?php
if (ini_get("magic_quotes_gpc")){
?>
	<p style="font-weight:bold;color:#F00;font-size:14px;margin-bottom:30px">Sie haben die PHP-Direktive magic_quotes_gpc aktiviert,dies führt
		dazu das die eMail-Vorlagen defekt gespeichert werden. Bitte deaktivieren Sie diese Funktion oder
		kontaktieren Sie Ihren Provider!</p>
<?php
}
if ($_GET["edit"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend>eMail-Vorlage bearbeiten</legend>
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_core_config_mails");
		
		
		$substitute = array("id"=>"hide",
		"name"=>"hide",
		"ishtml"=>"HTML eMail verschicken",
		"frommail"=>"Absender eMail",
		"fromname"=>"Absender Name",
		"subject"=>"Betreff",
		"content"=>"Plaintext",
		"contentHTML"=>"HTML-Text",
		"attachment"=>"eMail-Anhang",
		"htmlable"=>"hide",
		);
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	   if ($fieldName=="content"){
		   	  
		   	   		
		   	   	  	echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"width:350px\" cols=\"50\" rows=\"15\" class=\"w200\">{$getSite[$row["Field"]]}</textarea>
		   	   	  	";
		   	   	  	echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_mails","{$getSite["name"]}");
		   	   	  	echo "
		   	   	  	</li><li class=\"clear\"/>";
		   	   }elseif ($fieldName=="contentHTML"){
		   	   		if($getSite['htmlable'] != 1)
		   	   		{
		   	   			echo "<li><p style='margin-left:160px;'><b>HTML-Text wird in dieser Vorlage nicht unterstützt!</b></p></li>";
		   	   			echo "</li><li class=\"clear\"/>";
		   	   		}else{
		   	   			echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"width:350px\" cols=\"50\" rows=\"15\" class=\"w200\">{$getSite[$row["Field"]]}</textarea>
			   	   	  	";
			   	   	  	echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_mails","{$getSite["name"]}");
			   	   	  	echo "</li><li class=\"clear\"/>";
		   	   		}
			   	   	  	
		   	   }elseif ($fieldName=="attachment" && ($getSite["name"]=="sORDER" || $getSite["name"]=="sREGISTERCONFIRMATION")){
		   	   		// eMail-Attachment
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" />";
		   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_mails","{$getSite["name"]}");
		   	   		echo "</li><li class=\"clear\"/><li><strong>Format: lokal.pdf;Shop_AGB.pdf/lokal2.pdf;shop2.pdf (zuerst der Dateiname des Anhangs (muss unter /uploads/ liegen), dann der Name unter dem das Attachment in der eMail angezeigt werden soll! Trennung mehrerer Anhänge mit /</strong>";
		   	   		
		   	   		echo "</li><li class=\"clear\"/>";
		   	   }elseif ($fieldName=="ishtml"){
		   	   		if($getSite['htmlable'] != 1)
		   	   		{
		   	   			echo "<li><input id='ishtml' type='hidden' value='0' name='ishtml'/></li>";
		   	   		}else{
		   	   			if ($fieldName=="attachment") continue;
			   	   		$getSite[$row["Field"]] = htmlentities($getSite[$row["Field"]],ENT_COMPAT);
			   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" />";
			   	    	echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_mails","{$getSite["name"]}");
			   	   		echo "</li><li class=\"clear\"/>";
		   	   		}
		   	   }else {
		   	   		if ($fieldName=="attachment") continue;
		   	   		$getSite[$row["Field"]] = htmlentities($getSite[$row["Field"]],ENT_COMPAT);
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" />";
		   	    	echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_mails","{$getSite["name"]}");
		   	   		echo "</li><li class=\"clear\"/>";
		   	   }
		   	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->
		
		

 
        
		</ul>
        
        <div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["mails"]["textvorlagen_save"] ?></div></button>
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

