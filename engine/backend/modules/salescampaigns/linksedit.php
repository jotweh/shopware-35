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
	$deleteContainer = mysql_query("
	DELETE FROM s_emarketing_promotion_containers
	WHERE id={$_GET["delete"]}
	");
	// Delete childs
	$deleteChilds = mysql_query("DELETE FROM s_emarketing_promotion_links
	WHERE parentID={$_GET["delete"]}");
	
		echo "<script>";
	echo "parent.parent.Growl('".$sLang["salescampaigns"]["linkedit_link_deleted"]."');";
	echo "parent.myExt.reload();";
	echo "</script>";
	exit;
	
}

if (!$_GET["id"]) die ($sLang["salescampaigns"]["linkedit_linkgroup_not_found"]);


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
	
	
	


	
	if (!$_POST["sLinkName"]) $sError = $sLang["salescampaigns"]["linkedit_please_enter_a_linktitle"];
	if (!$_POST["sLink"] && !$sError) $sError = $sLang["salescampaigns"]["linkedit_please_enter_an_url"];
	
	
	if (!$sError){
		
			
	
			
			
			$sql = "
			INSERT INTO s_emarketing_promotion_links
			(parentID, description, link, target)
			VALUES (
			{$_GET["id"]},
			'{$_POST["sLinkName"]}',
			'{$_POST["sLink"]}',
			'{$_POST["sLinkTarget"]}'
			)";
			
			$insert = mysql_query($sql);
		
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["linkedit_link_cant_be_saved"];
			//echo $sql;
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["linkedit_link_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["linkedit_link_created"];
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["linkedit_should_this_linkgroup_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>

<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["linkedit_linkgroup_settings"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit2" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["linkedit_overview"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sHeadline"] ?>" name="sHeadline">
	</li>
	<li class="clear"></li>
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["linkedit_save"] ?></div></button></li>	
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="deleteCategory(<?php echo $_GET["id"] ?>,'<?php echo $category["description"]?>')" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["linkedit_delete_linkgroup"] ?></div></button></li>	
		</ul>
		</div>
	</ul>
		
</form>
		
</fieldset>

<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["linkedit_add_link_to_group"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm2" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["linkedit_linktitle"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sLinkName"] ?>" name="sLinkName">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["linkedit_directlink"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sLink"] ?>" name="sLink">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["linkedit_link_target"] ?></label>
		<select name="sLinkTarget">
		<option value="_parent"><?php echo $sLang["salescampaigns"]["linkedit_shopware"] ?></option>
		<option value="_blank" <?php if (count($_POST) && $_POST["sLinkTarget"]=="_blank") echo "selected";?>><?php echo $sLang["salescampaigns"]["linkedit_extern"] ?></option>
		</select>
	</li>
	<li class="clear"></li>
	
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm2').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["linkedit_add_link_to_group"] ?></div></button></li>	
		</ul>
		</div>
	
	</ul>
		
</form>
		
</fieldset>

</body>
</html>
