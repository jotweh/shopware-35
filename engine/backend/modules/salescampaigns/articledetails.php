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
	$query = mysql_query("
		DELETE FROM s_emarketing_promotion_articles WHERE id={$_GET["delete"]}
	");
	if ($query){
		echo "<script>";
		echo "parent.parent.Growl('";
		echo  $sLang["salescampaigns"]["articledetails_article_deleted"];
		echo "');";
		echo "parent.myExt.reload();";
		echo "</script>";
		die();
	}else {
		echo "<script>";
		echo "parent.parent.Growl('";
		echo $sLang["salescampaigns"]["articledetails_article_cant_be_deleted"];
		echo "');";
		echo "</script>";
	}
	
}
if (!$_GET["id"]) die ($sLang["salescampaigns"]["articledetails_article_not_found"]);



if ($_POST["sSubmit"]){
	
	
	


	
	if ($_POST["sTyp"]=="fix" && !$_POST["sOrder"]) $sError = $sLang["salescampaigns"]["articledetails_please_enter_ordernumber"];
	
	if ($_POST["sTyp"]=="fix" && !$sError){
		
		// Query Name
		$queryName = mysql_query("
		SELECT name FROM s_articles, s_articles_details
		WHERE s_articles.id = s_articles_details.articleID
		and s_articles_details.ordernumber='{$_POST["sOrder"]}'
		");
		
		if (!@mysql_num_rows($queryName)){
			$sError = $sLang["salescampaigns"]["articledetails_here_was_no_article_with_the_ordernumber"]." \"{$_POST["sOrder"]}\" ".$sLang["salescampaigns"]["articledetails_found"];
		}else {
			$_POST["sName"] = mysql_result($queryName,0,"name");
			
		}
	}
	
	if (!$sError){
		
			if ( $_POST["sTyp"]=="image"){
				if ($_FILES["sImgUpload"]["tmp_name"]){
					$result = upload("sImgUpload");
					
					if ($result=="WRONG FILE"){
						$sError = $sLang["promotion"]["promotion_inline_wrong_fileformat"];
					}else if ($result=="ERROR"){
						$sError = $sLang["promotion"]["promotion_inline_error_during_upload"];
					}else {
						$resultSQL = "image = '$result',";
					}
				}
			}
			
			
			if ($_POST["sTyp"]=="image"){
				$_POST["sName"] = "Grafik";
			}elseif (!$sError && $_POST["sTyp"]=="random"){
				$_POST["sName"] = "Zufall";
			}elseif (!$sError && $_POST["sTyp"]=="top"){
				$_POST["sName"] = "Topseller";
			}elseif (!$sError && $_POST["sTyp"]=="new"){
				$_POST["sName"] = "Neuheit";
			}elseif ($_POST["sTyp"]=="fix"){
				$queryName = mysql_query("
				SELECT name FROM s_articles, s_articles_details
				WHERE s_articles.id = s_articles_details.articleID
				and s_articles_details.ordernumber='{$_POST["sOrder"]}'
				");
				
				if (!@mysql_num_rows($queryName)){
					$sError = $sLang["salescampaigns"]["articledetails_here_was_no_article_with_the_ordernumber"]." \"{$_POST["sOrder"]}\" ".$sLang["salescampaigns"]["articledetails_found"];
				}else {
					$_POST["sName"] = mysql_result($queryName,0,"name");
					
				}
			}
			
			
			if (!$sError){
			
			
				$sql = "
				UPDATE s_emarketing_promotion_articles
				SET
				articleordernumber = '".mysql_real_escape_string($_POST["sOrder"])."', 
				name = '".mysql_real_escape_string($_POST["sName"])."',
				type = '{$_POST["sTyp"]}',
				$resultSQL
				link = '{$_POST["sImgLink"]}',
				target = '{$_POST["sImgTarget"]}'
				WHERE id={$_GET["id"]}
				";
			//	echo $sql;
				$insert = mysql_query($sql);
		
		
				if (!$insert){
					$sError = $sLang["salescampaigns"]["articledetails_article_cant_be_saved"];
					//echo $sql;
				}else {
					if ($_GET["id"]){
						$sInform = $sLang["salescampaigns"]["articledetails_article_updated"];
					}else {
						$sInform = $sLang["salescampaigns"]["articledetails_article_created"];
					}
				}
			}
	}
}


$queryArticle = mysql_query("
SELECT * FROM s_emarketing_promotion_articles
WHERE id={$_GET["id"]}
");
if (!@mysql_num_rows($queryArticle)){
	die ($sLang["salescampaigns"]["articledetails_article_not_found"]);
}else {
	$sArticle = mysql_fetch_array($queryArticle);
	$_POST["sTyp"] = $sArticle["type"];
	$_POST["sName"] = $sArticle["name"];
	$_POST["sOrder"] = $sArticle["articleordernumber"];
	$_POST["sImage"] = $sArticle["image"];
	$_POST["sImgLink"] = $sArticle["link"];
	$_POST["sImgTarget"] = $sArticle["target"];
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
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
	}
}
window.onload = function(){
	<?php
		if ($sInform){
			
			echo "parent.parent.Growl('$sInform');";
			//echo "parent.location.href = parent.location.href";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
function deleteArticle(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["articledetails_should_this_article_really_be_deleted"] ?>',window,'deleteArticle',ev);
}
</script>


<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["articledetails_edit_article"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["articledetails_ordernumber"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sOrder"] ?>" name="sOrder">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["articledetails_picture_upload"] ?></label>
	<input type="file" class="w200" style="height:20px" value="<?php echo $_POST["sImgUpload"] ?>" name="sImgUpload">
		<?php
	if ($_POST["sImage"]){
		echo "<br/><img src=\"../../../../images/banner/{$_POST["sImage"]}\" width=200 height=75 style=\"margin-left: 160px\">";
	}
	?>
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["articledetails_target_link"] ?></label>
		<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sImgLink"] ?>" name="sImgLink">
		<label style="width:30px"><?php echo $sLang["salescampaigns"]["articledetails_target"] ?></label><input type="text" class="w200" style="height:20px;width:120px;margin-left:10px" value="<?php echo $_POST["sImgTarget"] ?>" name="sImgTarget">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["articledetails_typ"] ?></label>
	<select name="sTyp">
	<option value="random" <?php echo $_POST["sTyp"] == "random" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articledetails_random"] ?></option>
	<option value="top" <?php echo $_POST["sTyp"] == "top" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articledetails_topseller"] ?></option>
	<option value="new" <?php echo $_POST["sTyp"] == "new" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articledetails_novelty"] ?></option>
	<option value="fix" <?php echo $_POST["sTyp"] == "fix" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articledetails_solid_article"] ?></option>
	<option value="image" <?php echo $_POST["sTyp"] == "image" ? "selected" : ""?>><?php echo $sLang["salescampaigns"]["articledetails_picture_with_link"] ?></option>
	</select>
	</li>
	<li class="clear"></li>
	
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["articledetails_save_article"] ?></div></button></li>	
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteArticle(<?php echo $_GET["id"] ?>,'<?php echo $category["description"]?>'); return false;" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["articledetails_delete_article"] ?></div></button></li>	
		</ul>
		</div>
	
	</ul>
		
</form>
		
</fieldset>

</body>
</html>
