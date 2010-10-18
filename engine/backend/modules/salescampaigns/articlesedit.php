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

function upload($field){
	$filename = $_FILES[$field]['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = strtolower($filename[count($filename)-1]);
	if ($filenameext!="gif" && $filenameext!="jpg" && $filenameext!="png" && $filenameext!="jpeg"){
		echo $filenameext;
		return "WRONG FILE";
	}
	$filename = $filename[0];
	// Random-Part for permit overwrite of existing files
	// (Article-ID | Download-Id)
	$filename = $filename."-".$_GET["id"].rand(0,10000).".".$filenameext;
	
		
	if (move_uploaded_file($_FILES[$field]['tmp_name'], "../../../../images/banner/".$filename)){
		return $filename;
	}else {
		return "ERROR";
	}	
}




if ($_GET["delete"]){
	$deleteContainer = mysql_query("
	DELETE FROM s_emarketing_promotion_containers
	WHERE id={$_GET["delete"]}
	");
	// Delete childs
	$deleteChilds = mysql_query("DELETE FROM s_emarketing_promotion_articles
	WHERE parentID={$_GET["delete"]}");
	
	echo "<script>";
	echo "parent.parent.Growl('Artikelgruppe wurde gelöscht');";
	echo "parent.myExt.reload();";
	echo "</script>";
	exit;
	
}

if (!$_GET["id"]) die ($sLang["salescampaigns"]["articlesedit_articlegroup_not_found"]);


if ($_POST["sSubmit2"]){
	$updateContainer = mysql_query("
	UPDATE s_emarketing_promotion_containers SET description='{$_POST["sHeadline"]}'
	WHERE id={$_GET["id"]}
	");
}

if ($_GET["id"]){
	$queryContainer = mysql_query("
	SELECT description FROM s_emarketing_promotion_containers
	WHERE id={$_GET["id"]}
	");
	if (@mysql_num_rows($queryContainer)){
		$_POST["sHeadline"] = mysql_result($queryContainer,0,"description");
	}
}

if ($_POST["sSubmit"]){
	

	if ( $_POST["sTyp"]=="image"){
		if ($_FILES["sImgUpload"]["tmp_name"]){
			$sImage = upload("sImgUpload");
		}else {
			$sError = $sLang["salescampaigns"]["articlesedit_no_picture_defined"];
		}
		if (!$sError){
			if ($sImage=="WRONG FILE"){
				$sError = $sLang["promotion"]["promotion_inline_wrong_fileformat"];
			}else if ($sImage=="ERROR"){
				$sError = $sLang["promotion"]["promotion_inline_error_during_upload"];
			}else {
				
			}
		}
		
	}

	
	if ($_POST["sTyp"]=="fix" && !$_POST["sOrder"]) $sError = $sLang["salescampaigns"]["articlesedit_please_enter_the_ordernumber"];
	
	if ($_POST["sTyp"]=="fix" && !$sError){
		
		// Query Name
		$queryName = mysql_query("
		SELECT name FROM s_articles, s_articles_details
		WHERE s_articles.id = s_articles_details.articleID
		and s_articles_details.ordernumber='{$_POST["sOrder"]}'
		");
		
		if (!@mysql_num_rows($queryName)){
			$sError = $sLang["salescampaigns"]["articlesedit_here_was_no_article_with_the_ordernumber"]." \"{$_POST["sOrder"]}\" ".$sLang["salescampaigns"]["articlesedit_found"];
		}else {
			$_POST["sName"] = mysql_result($queryName,0,"name");
			
		}
	}elseif (!$sError && $_POST["sTyp"]=="random"){
		$_POST["sName"] = "Zufall";
	}elseif (!$sError && $_POST["sTyp"]=="top"){
		$_POST["sName"] = "Topseller";
	}elseif (!$sError && $_POST["sTyp"]=="new"){
		$_POST["sName"] = "Neuheit";
	}elseif (!$sError && $_POST["sTyp"]=="image"){
		$_POST["sName"] = "Grafik";
	}
	
	if (!$sError){
		
			
	
			
			
			$sql = "
			INSERT INTO s_emarketing_promotion_articles
			(parentID, articleordernumber, name, type, image,link,target)
			VALUES (
			{$_GET["id"]},
			'".mysql_real_escape_string($_POST["sOrder"])."',
			'".mysql_real_escape_string($_POST["sName"])."',
			'{$_POST["sTyp"]}',
			'$sImage',
			'{$_POST["sImgLink"]}',
			'{$_POST["sImgTarget"]}'
			)";
			
			$insert = mysql_query($sql);
		
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["articlesedit_article_cant_be_saved"];
			//echo $sql;
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["articlesedit_article_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["articlesedit_article_created"];
			}
		}
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

<body>

  
  
    

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
		if ($sInform){
			
			echo "parent.parent.Growl('$sInform');";
			echo "parent.myExt.reload();";
			//echo "parent.location.href = parent.location.href";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
function deleteCategory(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["articlesedit_should_this_articlegroup_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>

<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["articlesedit_articlegroup_settings"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit2" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["articlesedit_heading"] ?></label>
		<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sHeadline"] ?>" name="sHeadline">
	</li>
	<li class="clear"></li>
	
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["articlesedit_save"] ?></div></button></li>	
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $_GET["id"] ?>,'<?php echo $category["description"]?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["articlesedit_delete_articlegroup"] ?></div></button></li>	
		</ul>
		</div>
	</ul>
		
</form>
		
</fieldset>


<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["articlesedit_add_article_to_group"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm2" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
		<li><label><?php echo $sLang["salescampaigns"]["articlesedit_ordernumber"] ?></label>
			<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sOrder"] ?>" name="sOrder">
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["articlesedit_picture_upload"] ?></label>
			<input type="file" class="w200" style="height:20px" value="<?php echo $_POST["sImgUpload"] ?>" name="sImgUpload">
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["articlesedit_linktarget"] ?></label>
				<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sImgLink"] ?>" name="sImgLink">
				
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["articlesedit_target"] ?></label>
		<input type="text" class="w200" style="height:20px;" value="<?php echo $_POST["sImgTarget"] ?>" name="sImgTarget">
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["articlesedit_typ"] ?></label>
			<select name="sTyp">
			<option value="random" <?php echo $_POST["sTyp"] == "random" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articlesedit_random"] ?></option>
			<option value="top" <?php echo $_POST["sTyp"] == "top" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articlesedit_topseller"] ?></option>
			<option value="new" <?php echo $_POST["sTyp"] == "new" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articlesedit_novelty"] ?></option>
			<option value="fix" <?php echo $_POST["sTyp"] == "fix" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articlesedit_solid_article"] ?></option>
			<option value="image" <?php echo $_POST["sTyp"] == "image" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articlesedit_picture_with_link"] ?></option>
			</select>
		</li>
		<li class="clear"></li>
		
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm2').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["articlesedit_add_article_to_group"] ?></div></button></li>	
		</ul>
		</div>
		
	</ul>
		
</form>
		
</fieldset>

</body>
</html>
