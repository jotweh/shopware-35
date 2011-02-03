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

if ($_GET["category"] && $_GET["category"]!=1){
	$getCategoryName = mysql_query("
	SELECT description FROM s_categories WHERE id={$_GET["category"]}
	");
	$categoryName = @mysql_result($getCategoryName,0,"description");
	if (!$categoryName) die($sLang["salescampaigns"]["campaignsedit_category_not_found"]);
}elseif ($_GET["category"]==1){
	$categoryName = $sLang["salescampaigns"]["campaignsedit_startsite"];
}
// Doing
if ($_GET["delete"]){
	// Delete all
	$queryDeleteMain = mysql_query("
	DELETE FROM s_emarketing_promotion_main
	WHERE id={$_GET["delete"]}
	");
	if ($queryDeleteMain){
		$queryAllContainer = mysql_query("
		SELECT * FROM s_emarketing_promotion_containers
		WHERE promotionID={$_GET["delete"]}
		");
		
		while ($container = mysql_fetch_array($queryAllContainer)){
			// Delete Container
			$deleteContainer = mysql_query("
			DELETE FROM s_emarketing_promotion_containers
			WHERE id = {$container["id"]}
			");
			
			if ($deleteContainer){
				switch ($container["type"]){
					case "ctBanner":
						$deleteBanner = mysql_query("
						DELETE FROM s_emarketing_promotion_banner WHERE parentID={$container["id"]}
						");
						break;
					case "ctArticles":
						$deleteBanner = mysql_query("
						DELETE FROM s_emarketing_promotion_articles WHERE parentID={$container["id"]}
						");
						break;
					case "ctLinks":
						$deleteBanner = mysql_query("
						DELETE FROM s_emarketing_promotion_links WHERE parentID={$container["id"]}
						");
						break;
					case "CtText":
						$deleteBanner = mysql_query("
						DELETE FROM s_emarketing_promotion_html WHERE parentID={$container["id"]}
						");
						break;
				}
			}else {
				echo $sLang["salescampaigns"]["campaignsedit_container_delete_failed"];
			}
		}
	}else {
		echo $sLang["salescampaigns"]["campaignsedit_action_delete_failed"];
	}
	
	echo "<script>";
	echo "parent.parent.Growl('".$sLang["salescampaigns"]["campaignsedit_action_deleted"]."');";
	echo "parent.myExt.reloadParent();";
	echo "</script>";
	exit;
}


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
	$filename = preg_replace("/[^a-zA-Z0-9]/","",$filename);
	// Remove 
	
	
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

if ($_POST["sContainer"] && $_GET["id"]){

	// Insert container
	$insertContainer = mysql_query("
	INSERT INTO s_emarketing_promotion_containers (promotionID, type)
	VALUES ({$_GET["id"]}, '{$_POST["sContainer"]}')
	");
	
	echo "
	<script>
	parent.myExt.reload();
	</script>
	";
	
}


if ($_POST["sSubmit"]){
	
	
	if ($_FILES["sImage"]["tmp_name"]){
		$result = upload("sImage");
	}else {
		$result = "";
	}
	if ($result=="WRONG FILE"){
		$sError = $sLang["salescampaigns"]["campaignsedit_wrong_file_format"];
	}else if ($result=="ERROR"){
		$sError= $sLang["salescampaigns"]["campaignsedit_upload_failed"];
	}

	// Reformating dates
	if ($_POST["sStart"]){
		$_POST["sStart"] = explode(".",$_POST["sStart"]);
		$_POST["sStart"] = $_POST["sStart"][2]."-".$_POST["sStart"][1]."-".$_POST["sStart"][0];
	}	
	if ($_POST["sEnd"]){
		$_POST["sEnd"] = explode(".",$_POST["sEnd"]);
		$_POST["sEnd"] = $_POST["sEnd"][2]."-".$_POST["sEnd"][1]."-".$_POST["sEnd"][0];
	}

	// Manage file-uploads

	if (!$_POST["sActive"]) $_POST["sActive"] = "0";
	if (!$_POST["sName"]) $sError = $sLang["salescampaigns"]["campaignsedit_please_enter_title"];
	
	if (!$sError){
	
		if ($_GET["id"]){ // Edit
			if ($result){
				$imageQuery = "image='$result',";
			}
			$insert = mysql_query("
			UPDATE s_emarketing_promotion_main
			SET
			positionGroup='{$_POST["sPosition"]}',
			start='{$_POST["sStart"]}',
			end='{$_POST["sEnd"]}',
			$imageQuery
			description='{$_POST["sName"]}',
			link='{$_POST["sLink"]}',
			linktarget='{$_POST["sLinkTarget"]}',
			active={$_POST["sActive"]}
			WHERE id={$_GET["id"]}
			");
		}else {
			$insert = mysql_query("
			INSERT INTO s_emarketing_promotion_main
			(parentID, positionGroup, datum, start, end, image, description, link, linktarget, active )
			VALUES (
			{$_GET["category"]},
			'{$_POST["sPosition"]}',
			now(),
			'{$_POST["sStart"]}',
			'{$_POST["sEnd"]}',
			'$result',
			'{$_POST["sName"]}',
			'{$_POST["sLink"]}',
			'{$_POST["sLinkTarget"]}',
			{$_POST["sActive"]}
			)
			");
		}
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["campaignsedit_cant_save_action"];
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["campaignsedit_action_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["campaignsedit_action_created"];
			}
		}
	}
}


if ($_GET["id"]){
	$queryCampaign = mysql_query("
	SELECT positionGroup,datum, start, end, image, description, link, linktarget, active FROM s_emarketing_promotion_main
	WHERE id={$_GET["id"]}
	");
	if (@mysql_num_rows($queryCampaign)){
		$queryCampaign = mysql_fetch_array($queryCampaign);
		
		$_POST["sStart"] = $queryCampaign["start"];
		$_POST["sEnd"] = $queryCampaign["end"];
		
		if ($_POST["sStart"]=="0000-00-00"){
			unset($_POST["sStart"]);
		}else {
			$_POST["sStart"] = explode("-",$_POST["sStart"]);
			$_POST["sStart"] = $_POST["sStart"][2].".".$_POST["sStart"][1].".".$_POST["sStart"][0];
		}
		
		if ($_POST["sEnd"]=="0000-00-00"){
			unset($_POST["sEnd"]);
		}else {
			$_POST["sEnd"] = explode("-",$_POST["sEnd"]);
			$_POST["sEnd"] = $_POST["sEnd"][2].".".$_POST["sEnd"][1].".".$_POST["sEnd"][0];
		}
		
		
		
		$_POST["sImage"] = $queryCampaign["image"];
		$_POST["sName"] = $queryCampaign["description"];
		$_POST["sLink"] = $queryCampaign["link"];
		$_POST["sLinkTarget"] = $queryCampaign["linktarget"];
		$_POST["sActive"] = $queryCampaign["active"];
		$_POST["sPosition"] = $queryCampaign["positionGroup"];
		
	}else {
		die ($sLang["salescampaigns"]["campaignsedit_action_not_found"]);
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
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
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
			echo "parent.myExt.reload();";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};
function deleteCategory(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["campaignsedit_should_the_action"] ?> "'+text+'" <?php echo $sLang["salescampaigns"]["campaignsedit_should_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>


<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["campaignsedit_action"]." {$_POST["sName"]} ".$sLang["salescampaigns"]["campaignsedit_edit"];
}else {
	echo $sLang["salescampaigns"]["campaignsedit_New_action_in"]." \"$categoryName\" ".$sLang["salescampaigns"]["campaignsedit_create"];
}
function cleanup_url ($url){
		$url = str_replace(" ","-",$url);
		$url= preg_replace("/%[0-9][0-9]/","-",urlencode($url));
		
		// Umlaute
		$replaceRules = array(
		"%E4"=>"ae",
		"%F6"=>"oe",
		"%FC"=>"ue",
		"%DC"=>"Ue",
		"%C4"=>"Ae",
		"%D6"=>"Oe",
		"%DF"=>"ss",
		"quot"=>"Zoll",
		"%3B"=>"",
		"%2F"=>"-",
		"%3A"=>"-",
		"%B4"=>"",
		"%2B"=>"",
		"%2C"=>"-",
		"%27"=>"Zoll",
		"amp"=>"",
		"---"=>"-",
		"--"=>"-"
		);
		
		foreach ($replaceRules as $replaceRule => $replaceSubstitution) $url = str_replace($replaceRule,$replaceSubstitution,$url);
		
		return $url;
	}
?>
</legend>
<?php
if ($licenceFailed && !$_GET["id"]){
	echo "<strong>".$sLang["salescampaigns"]["campaignsedit_In_the_unlicensed_version"]."</strong>";
}else {
?>
	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&category=<?php echo $_GET["category"] ?>">
	<input type="hidden" name="sSubmit" value="1">
	<ul>
	<!-- General options -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_Name_of_the_Action"] ?></label>
		<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sName"] ?>" name="sName">
	</li>
	<li class="clear"></li>
	<?php
	if ($_GET["id"] && !$_POST["sLink"]){
	?>
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_Link_to_the_Action"] ?></label>
		<a href="http://<?php echo $sCore->sCONFIG['sBASEPATH']?>/<?php echo cleanup_url($_POST["sName"]) ?>_campaign_<?php echo $_GET["id"] ?>.html" target="_blank"><?php echo $sLang["salescampaigns"]["campaignsedit_open"] ?></a>
	</li>
	<li class="clear"></li>
	<?php
	}
	?>
	<!-- Start -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_position"] ?></label>
			<select name="sPosition">
			<?php
			$positions = $sCore->sCONFIG["sCAMPAIGNSPOSITIONS"];
			$positions = explode(";",$positions);
			foreach ($positions as $position){
				$positionData = explode(":",$position);
				$selected = $_POST["sPosition"]==$positionData[1] ? "selected" : "";
				
				echo "<option value=\"{$positionData[1]}\" $selected>{$positionData[0]}</option>";
			}
			?>
			
			</select>
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_valid_from"] ?></label>
		<input id="sStart" type="text" class="w200" style="height:20px" value="<?php echo $_POST["sStart"] ?>" name="sStart"><a class="ico calendar"  onclick="displayDatePicker('sStart', false, 'dmy', '.');"></a>
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_valid_until"] ?></label>
		<input id="sEnd" type="text" class="w200" style="height:20px" value="<?php echo $_POST["sEnd"] ?>" name="sEnd"><a class="ico calendar"  onclick="displayDatePicker('sEnd', false, 'dmy', '.');"></a>
	</li>
	<li class="clear"></li>
	<!-- Ende -->
	
	<!-- Image -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_picture"] ?></label>
		<input id="sEnd" type="file" class="w200" style="height:20px" name="sImage">
		<?php
		if ($_POST["sImage"]){
			echo "<br /><img src=\""."../../../../images/banner/".$_POST["sImage"]."\" width=100 height=50 style=\"margin-left:160px\">";
		}
		?>
	</li>
	<li class="clear"></li>
	
	<!-- Direktlink -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_directlink"] ?></label>
	<input id="sEnd" type="input" class="w200" style="height:20px" value="<?php echo $_POST["sLink"] ?>" name="sLink"><br />
	<label> </label><span style="font-weight:bold"><?php echo $sLang["salescampaigns"]["campaignsedit_Disabled_container"] ?></span>
	</li>
	<li class="clear"></li>
	
	<!-- Link - Ziel  -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_link_target"] ?></label>
		<select name="sLinkTarget">
		<option value="_parent"><?php echo $sLang["salescampaigns"]["campaignsedit_shopware"] ?></option>
		<option value="_blank" <?php if (count($_POST) && $_POST["sLinkTarget"]=="_blank") echo "selected";?>><?php echo $sLang["salescampaigns"]["campaignsedit_extern"] ?></option>
		</select>
	</li>
	<li class="clear"></li>
	
	<!-- Aktiv, Ja, Nein -->
	<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_activ"] ?></label>
		<select name="sActive">
		<option value="1"><?php echo $sLang["salescampaigns"]["campaignsedit_yes"] ?></option>
		<option value="0" <?php if (count($_POST) && !$_POST["sActive"]) echo "selected";?>><?php echo $sLang["salescampaigns"]["campaignsedit_no"] ?></option>
		</select>
	</li>
	<li class="clear"></li>
	
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["campaignsedit_save"] ?></div></button></li>	
		</ul>
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $_GET["id"] ?>,'<?php echo $_POST["sName"] ?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["campaignsedit_delete_action"] ?></div></button></li>	
		</ul>
	</div>
	
	</ul>
	<!-- // General -->
	

	<!--  Container Start -->

	<!-- Container End -->
	
</form>
		
</fieldset>
	<?php
	} // LICENCE CHECK
	?>
<?php
if ($_GET["id"]){
?>
	<div class="clear"></div>

	
	<fieldset>
	<legend><a class="ico folder_add"></a> <?php echo $sLang["salescampaigns"]["campaignsedit_add_container"] ?> </legend>
	<form name="newContainer" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
		<input type="hidden" name="sSubmitContainer" value="1">
		
		<ul>
		<li><label><?php echo $sLang["salescampaigns"]["campaignsedit_new_container"] ?></label>
		<select name="sContainer">
		
		<option><?php echo $sLang["salescampaigns"]["campaignsedit_please_select"] ?></option>
		<option value="ctBanner"><?php echo $sLang["salescampaigns"]["campaignsedit_banner"] ?></option>
		<option value="ctText"><?php echo $sLang["salescampaigns"]["campaignsedit_html_text"] ?></option>
		<option value="ctArticles"><?php echo $sLang["salescampaigns"]["campaignsedit_articlegroup"] ?></option>
		<option value="ctLinks"><?php echo $sLang["salescampaigns"]["campaignsedit_linkgroup"] ?></option>
		
		</select>
		</li>
		<li class="clear"></li>
		
			<div class="buttons" id="buttons">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["campaignsedit_Insert_container"] ?></div></button></li>	
			</ul>
			</div>
		
		
		</ul>
		
	</form>
	</fieldset>
<?php
}
?>
</body>
</html>
