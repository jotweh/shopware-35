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
}else {
	
}
if ($_GET["delete"]){
	$sql = "
	DELETE FROM s_emarketing_promotion_containers
	WHERE id={$_GET["delete"]}
	";

	$deleteContainer = mysql_query($sql);

	
	$deleteChild = mysql_query("
	DELETE FROM s_emarketing_promotion_banner
	WHERE parentID={$_GET["delete"]}
	");
	
	
		echo "<script>";
	echo "parent.parent.Growl('".$sLang["salescampaigns"]["banneredit_banner_deleted"]."');";
	echo "parent.myExt.reload();";
	echo "</script>";
	exit;
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
		chmod("../../../../images/banner/".$filename,0777);
		return $filename;
	}else {
		return "ERROR";
	}	
}


if ($_POST["sSubmit"]){
	
	
	if ($_FILES["sImage"]["tmp_name"]){
		$result = upload("sImage");
	}else {
		$result = "";
	}
	if ($result=="WRONG FILE"){
		$sError = $sLang["salescampaigns"]["banneredit_wrong_file_format"];
	}else if ($result=="ERROR"){
		$sError= $sLang["salescampaigns"]["banneredit_upload_failed"];
	}

	if (!$_POST["sName"]) $sError = $sLang["salescampaigns"]["banneredit_please_enter_a_title"];
	
	if (!$sError){
		
		$queryCampaign = mysql_query("
		SELECT image, description, link, linktarget FROM s_emarketing_promotion_banner
		WHERE parentID={$_GET["id"]}
		");
	
		if (mysql_num_rows($queryCampaign)){ // Edit
			if ($result){
				$imageQuery = "image='$result',";
			}
			$insert = mysql_query("
			UPDATE s_emarketing_promotion_banner
			SET
			$imageQuery
			description='{$_POST["sName"]}',
			link='{$_POST["sLink"]}',
			linktarget='{$_POST["sLinkTarget"]}'
			WHERE parentID={$_GET["id"]}
			");
		}else {
			$insert = mysql_query("
			INSERT INTO s_emarketing_promotion_banner
			(parentID, image, description, link, linktarget )
			VALUES (
			{$_GET["id"]},
			'$result',
			'{$_POST["sName"]}',
			'{$_POST["sLink"]}',
			'{$_POST["sLinkTarget"]}'
			)
			");
		}
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["banneredit_banner_cant_be_saved"];
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["banneredit_banner_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["banneredit_banner_created"];
			}
		}
	}
}


if ($_GET["id"]){
	$queryCampaign = mysql_query("
	SELECT image, description, link, linktarget FROM s_emarketing_promotion_banner
	WHERE parentID={$_GET["id"]}
	");
	if (@mysql_num_rows($queryCampaign)){
		$queryCampaign = mysql_fetch_array($queryCampaign);
			
		
		$_POST["sImage"] = $queryCampaign["image"];
		$_POST["sName"] = $queryCampaign["description"];
		$_POST["sLink"] = $queryCampaign["link"];
		$_POST["sLinkTarget"] = $queryCampaign["linktarget"];

		
		
	}else {
	//	die ("Banner nicht gefunden");
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["banneredit_should_the_category"] ?>"'+text+'" <?php echo $sLang["salescampaigns"]["banneredit_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>


<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["banneredit_edit_banner"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
	<!-- General options -->
		<li><label><?php echo $sLang["salescampaigns"]["banneredit_bannertitle"] ?></label>
			<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sName"] ?>" name="sName">
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["banneredit_picture"] ?></label>
				<input id="sEnd" type="file" class="w200" style="height:20px" name="sImage">
				<?php
				if ($_POST["sImage"]){
					echo "<br/><img src=\""."../../../../images/banner/".$_POST["sImage"]."\" width=200 style=\"margin-left: 160px\">";
				}
				?>
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["banneredit_directlink"] ?></label>
			<input id="sEnd" type="input" class="w200" style="height:20px" value="<?php echo $_POST["sLink"] ?>" name="sLink"><br />
		</li>
		<li class="clear"></li>
		
		<li><label><?php echo $sLang["salescampaigns"]["banneredit_linktarget"] ?></label>
			<select name="sLinkTarget">
			<option value="_parent"><?php echo $sLang["salescampaigns"]["banneredit_shopware"] ?></option>
			<option value="_blank" <?php if (count($_POST) && $_POST["sLinkTarget"]=="_blank") echo "selected";?>><?php echo $sLang["salescampaigns"]["banneredit_extern"] ?></option>
			</select>
		</li>
		<li class="clear"></li>
		
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["banneredit_save"] ?></div></button></li>	
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $_GET["id"] ?>,'<?php echo $category["description"]?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["banneredit_delete_banner"] ?></div></button></li>	
		</ul>
		</div>
		
		
	</ul>

</form>
		
</fieldset>

</body>
</html>
