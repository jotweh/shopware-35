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

	// Delete childs
	$deleteChilds = mysql_query("DELETE FROM s_emarketing_promotion_links
	WHERE id={$_GET["delete"]}");
	
	echo $sLang["salescampaigns"]["linkdetails_link_deleted"];
	
}

if (!$_GET["id"]) die ($sLang["salescampaigns"]["linkdetails_link_not_found"]);





if ($_POST["sSubmit"]){

	if (!$_POST["sLinkName"]) $sError = $sLang["salescampaigns"]["linkdetails_enter_linktitle"];
	if (!$_POST["sLink"] && !$sError) $sError = $sLang["salescampaigns"]["linkdetails_please_enter_URL"];
	
	
	if (!$sError){
		
			$sql = "
			UPDATE s_emarketing_promotion_links
			SET 
			description = '{$_POST["sLinkName"]}',
			link = '{$_POST["sLink"]}',
			target = '{$_POST["sLinkTarget"]}'
			WHERE id = {$_GET["id"]}
			";
			
			//echo $sql;
			
			$insert = mysql_query($sql);
		
		
		if (!$insert){
			$sError = $sLang["salescampaigns"]["linkdetails_link_cant_be_saved"];
			//echo $sql;
		}else {
			if ($_GET["id"]){
				$sInform = $sLang["salescampaigns"]["linkdetails_link_updated"];
			}else {
				$sInform = $sLang["salescampaigns"]["linkdetails_link_created"];
			}
		}
	}
}

if ($_GET["id"]){
	// Fetch link
	$queryLink = mysql_query("
	SELECT description, link, target FROM s_emarketing_promotion_links
	WHERE id = {$_GET["id"]}
	");
	
	if (!@mysql_num_rows($queryLink)){
		die ($sLang["salescampaigns"]["linkdetails_link_not_found"]);
	}else {
		$linkData = mysql_fetch_array($queryLink);
		$_POST["sLinkName"] = $linkData["description"];
		$_POST["sLink"] = $linkData["link"];
		$_POST["sLinkTarget"] = $linkData["target"];
		
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
		parent.parent.sConfirmationObj.show('<?php echo $sLang["salescampaigns"]["linkdetails_link_should_this_link_really_be_deleted"] ?>',window,'deleteCategory',ev);
}
</script>

<fieldset>
<legend><a class="ico folder_add"></a> 
<?php
if ($_GET["id"]){
	echo $sLang["salescampaigns"]["linkdetails_edit_link"];
}
?>
</legend>

	<form name="newCampaign" method="POST" enctype="multipart/form-data" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
	<input type="hidden" name="sSubmit" value="1">
	
	<ul>
	<li><label><?php echo $sLang["salescampaigns"]["linkdetails_linktitle"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sLinkName"] ?>" name="sLinkName">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["linkdetails_direktlink"] ?></label>
	<input type="text" class="w200" style="height:20px" value="<?php echo $_POST["sLink"] ?>" name="sLink">
	</li>
	<li class="clear"></li>
	
	<li><label><?php echo $sLang["salescampaigns"]["linkdetails_linktarget"] ?></label>
	<select name="sLinkTarget">
	<option value="_parent"><?php echo $sLang["salescampaigns"]["linkdetails_shopware"] ?></option>
	<option value="_blank" <?php if (count($_POST) && $_POST["sLinkTarget"]=="_blank") echo "selected";?>><?php echo $sLang["salescampaigns"]["linkdetails_extern"] ?></option>
	</select>
	</li>
	<li class="clear"></li>
		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["salescampaigns"]["linkdetails_save_link"] ?></div></button></li>	
		</ul>
		</div>
	
	</ul>
		
</form>
		
</fieldset>

</body>
</html>
