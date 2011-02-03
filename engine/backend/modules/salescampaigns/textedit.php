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
// Doing
if (preg_match("/:/",$_GET["id"])){
	$_GET["id"] = explode(":",$_GET["id"]);
	$_GET["id"] = $_GET["id"][1];
}
if ($_GET["delete"]){
	$sql = "
	DELETE FROM s_emarketing_promotion_containers
	WHERE id={$_GET["delete"]}
	";

	$deleteContainer = mysql_query($sql);

	
	$deleteChild = mysql_query("
	DELETE FROM s_emarketing_promotion_html
	WHERE parentID={$_GET["delete"]}
	");
	
	
		echo "<script>";
	echo "parent.parent.Growl('".$sLang["salescampaigns"]["textedit_text_deleted"]."');";
	echo "parent.myExt.reload();";
	echo "</script>";
	exit;
}



if ($_POST["sSubmit"]){
	
	
	$_POST["sText"] = mysql_real_escape_string($_POST["sText"]);
	
	if (!$_POST["sName"]) $sError = $sLang["salescampaigns"]["textedit_please_enter_a_title"];
	
	if (!$sError){
		
		$queryCampaign = mysql_query("
		SELECT id FROM s_emarketing_promotion_html
		WHERE parentID={$_GET["id"]}
		");
	
		if (mysql_num_rows($queryCampaign)){ // Edit
		
			$sql = "
			UPDATE s_emarketing_promotion_html
			SET
			headline='{$_POST["sName"]}',
			html='{$_POST["sText"]}'
			WHERE parentID={$_GET["id"]}
			";
			$insert = mysql_query($sql);
		}else {
			$sql = "
			INSERT INTO s_emarketing_promotion_html
			(parentID, headline, html)
			VALUES (
			{$_GET["id"]},
			'{$_POST["sName"]}',
			'{$_POST["sText"]}'
			)
			";
			$insert = mysql_query($sql);
		}
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["textedit_text_cant_be_saved"];
			//echo $sql;
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["textedit_text_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["textedit_text_created"];
			}
		}
	}
}


if ($_GET["id"]){
	$queryCampaign = mysql_query("
	SELECT headline, html FROM s_emarketing_promotion_html
	WHERE parentID={$_GET["id"]}
	");
	if (@mysql_num_rows($queryCampaign)){
		$queryCampaign = mysql_fetch_array($queryCampaign);
			
		
		$_POST["sName"] = $queryCampaign["headline"];
		$_POST["sText"] = $queryCampaign["html"];


		
		
	}else {
		//die ("Banner nicht gefunden");
	}
	
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title></title>
<!-- Common Styles for the examples -->
</head>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="js/calendar.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>
<body>
<script language="javascript" type="text/javascript"> 
  	tinyMCE.init({
		// General options
		mode: "textareas",
		elements : "text,test",
		theme : "advanced",
		<?php echo$sCore->sCONFIG['sTINYMCEOPTIONS']?>, 
		extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]",
		//cleanup : false, skin : "o2k7", relative_urls : false,theme_advanced_resizing : true, theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left",	theme_advanced_path_location : "bottom",
		plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template,imagemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor"	
	});
	
	function tinyBrowser (field_name, url, type, win) {
		
       /* If you work with sessions in PHP and your client doesn't accept cookies you might need to carry
          the session name and session ID in the request string (can look like this: "?PHPSESSID=88p0n70s9dsknra96qhuk6etm5").
          These lines of code extract the necessary parameters and add them back to the filebrowser URL again. */
	   type = "image";
       var cmsURL = "../../../vendor/tinymce/backend/plugins/tinybrowser/tinybrowser.php";    // script URL - use an absolute path!
       if (cmsURL.indexOf("?") < 0) {
           //add the type as the only query parameter
           cmsURL = cmsURL + "?type=" + type;
       }
       else {
           //add the type as an additional query parameter
           // (PHP session ID is now included if there is one at all)
           cmsURL = cmsURL + "&type=" + type;
       }

       tinyMCE.activeEditor.windowManager.open({
           file : cmsURL,
           title : 'Tiny Browser',
           width : 650, 
           height : 440,
           resizable : "yes",
           scrollbars : "yes",
           inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
           close_previous : "no"
       }, {
           window : win,
           input : field_name
       });
       return false;
     }
</script>

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
			echo "parent.location.href = parent.location.href";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
function deleteCategory(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["textedit_should_this_text_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>


<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["textedit_edit_text"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["textedit_heading"] ?></label>
		<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sName"] ?>" name="sName">
	</li>
		<li class="clear"></li>
		
	<li><?php echo $sLang["salescampaigns"]["textedit_text"] ?><br/>
		<textarea name="sText"><?php echo $_POST["sText"]?></textarea>
	</li>
		<li class="clear"></li>
		
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["textedit_save"] ?></div></button></li>	
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $_GET["id"] ?>,'<?php echo $category["description"]?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["textedit_delete_text"] ?></div></button></li>	
		</ul>
		</div>
	</ul>
	
</form>
		
</fieldset>

</body>
</html>
