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


if ($_GET["delete"]){
	/*$delete = mysql_query("
	DELETE FROM s_cms_static WHERE id={$_GET["delete"]}
	");
	*/
	$sInform = $sLang["auth"]["auth_new_site_deleted"];
}


if ($_POST["sAction"]=="saveArticle"){
	// Check dependencies
		
		
	//if (!$_POST["description"]) $sError = "Bitte geben Sie eine Überschrift ein";
	
	
	if (!$sError){
		
		if (!$_POST["username"] || !$_POST["name"] || !$_POST["email"]){
			$sError = $sLang["auth"]["auth_new_Please_fill_in_all_fields"];
		}
		
		if ($_GET["edit"]){

			if ($_POST["password"]){
				
				
				$_POST["password"] = md5("A9ASD:_AD!_=%a8nx0asssblPlasS$".md5($_POST["password"]));
				$passSQL = "password='{$_POST["password"]}',";	
			}
			
			$sql = "
			UPDATE s_core_auth
			SET
			username='{$_POST["username"]}',
			$passSQL
			name='{$_POST["name"]}',
			sidebar=".intval($_POST["sidebar"]).",
			email='{$_POST["email"]}',
			active='{$_POST["active"]}',
			admin='{$_POST["admin"]}'
			WHERE id={$_GET["edit"]}
			";
		
			
			$insertArticle = mysql_query($sql);
			
			
			/*$insertArticle = mysql_query("
			UPDATE s_cms_content SET
			description='{$_POST["sCmsHeadline"]}',
			text='{$_POST["sCmsText"]}',
			link='{$_POST["sCmsLink"]}',
			datum='{$_POST["sCmsDatum"]}'
			$picture
			$file
			WHERE id={$_GET["edit"]}
			");*/
			
		}else {
			$_POST["password"] = md5("A9ASD:_AD!_=%a8nx0asssblPlasS$".md5($_POST["password"]));
			
			$sql = "
			INSERT INTO s_core_auth
			(username, password, name, email, active, sidebar, admin,salted)
			VALUES ('{$_POST["username"]}','{$_POST["password"]}','{$_POST["name"]}','{$_POST["email"]}', 1, 1,'{$_POST["admin"]}',1)
			";
		
			$insertArticle = mysql_query($sql);
		}
		
		if ($insertArticle){
			if ($_GET["edit"]){
				$sInform = $sLang["auth"]["auth_new_user_saved"];
			}else {
				$sInform = $sLang["auth"]["auth_new_user_created"];
			}
		}else {
			echo mysql_error();
		}
	}
}

if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_core_auth WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["auth"]["auth_new_user_not_found"];
		$abort = true;
	}else {		
		$getSite = mysql_fetch_array($getSite);
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

<script language="javascript" type="text/javascript">

</script>


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
		parent.sConfirmationObj.show('<?php echo $sLang["auth"]["auth_new_should_the_site"] ?> "'+text+'" <?php echo $sLang["auth"]["auth_new_really_be_deleted"] ?>',window,'deleteArticle',ev);
	}
window.onload = function(){
	<?php
		if ($sInform){
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
</script>
<?php if ($abort) exit; ?>
<form  method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $_GET["edit"] ? $sLang["auth"]["auth_new_user"]." \"{$getSite["name"]}\" ".$sLang["auth"]["auth_new_edit_last_online"]." {$getSite["lastlogin"]})" : $sLang["auth"]["auth_new_create_user"]?></legend>
		
		<ul>
		
	
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_core_auth");
		
		if ($_GET["edit"]){
			$substitute = array("id"=>"hide",
			"sessionID"=>"hide",
			"lastlogin"=>"hide",
			"active"=>$sLang["auth"]["auth_new_active_1"],
			"username"=>$sLang["auth"]["auth_new_username"],
			"password"=>$sLang["auth"]["auth_new_password"],
			"name"=>$sLang["auth"]["auth_new_name"],
			"email"=>$sLang["auth"]["auth_new_email"],
			"window_height"=>"hide",
			"window_width"=>"hide",
			"window_size"=>"hide",
			"rights"=>"hide",
			"salted"=>"hide"
			);
		}else {
			$substitute = array("id"=>"hide",
			"sessionID"=>"hide",
			"lastlogin"=>"hide",
			"active"=>"hide",
			"username"=>$sLang["auth"]["auth_new_username"],
			"password"=>$sLang["auth"]["auth_new_password"],
			"name"=>$sLang["auth"]["auth_new_name"],
			"email"=>$sLang["auth"]["auth_new_email"],
			"window_height"=>"hide",
			"window_width"=>"hide",
			"window_size"=>"hide",
			"rights"=>"hide",
			"salted"=>"hide"
			);
		}
		if (!$_GET["edit"]){
			$substitute["window_height"]="hide";
			$substitute["window_width"]="hide";
			$substitute["sidebar"]="hide";
		};
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
		   	   
		   	   if ($fieldName=="password") $getSite[$row["Field"]] = "";
				
		   	   if ($fieldName=="additionaldescription"){
		   	   //	$getSite[$row["Field"]] = 
		   	   	  echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"width:350px\" cols=\"50\" rows=\"5\" class=\"w200\">{$getSite[$row["Field"]]}</textarea></li><li class=\"clear\"/>";
		   	   }elseif ($fieldName=="sidebar"){	
		   	   	?>
		   	   	<li>
		   	   	<label style="width:150px; text-align:left;" for="name"><?php echo $column?>:</label>
		   	   	<input type="radio"<?php if ($getSite[$row["Field"]] != 1) echo " checked";?> style="float: none;" value="0" name="<?php echo $fieldName?>"/><?php echo $sLang["auth"]["auth_new_dont_show"] ?>
		   	   	<input type="radio"<?php if ($getSite[$row["Field"]] == 1) echo " checked";?> style="float: none;" value="1" name="<?php echo $fieldName?>"/><?php echo $sLang["auth"]["auth_new_show"] ?>
		   	   	<li class="clear"/>
		   	   	<?php
		   	   }else {
		   	   
		   	   echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"height:25px;width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" /></li>";
		   	    echo "<li class=\"clear\"/>";
		   	   }
		       
		        
		      
		       
		      

		   	}

	   }

		
		?>	
		<!-- // Felder ausgeben -->

		</ul>
		
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["auth"]["auth_new_save"] ?></div></button>
			</li>	
		</ul>
	</div>
		</fieldset>
		</form>
<?php
if ($_GET["edit"]){
?>
<fieldset>
<legend><?php echo $sLang["auth"]["auth_new_Module_access_rights"] ?></legend>
<?php

if ($_GET["submitRights"]){
	foreach ($_POST as $key => $value){
		foreach ($value as $id){
			$rights[] = $id;
		}
	}
	if (count($rights)){
		$rights = serialize($rights);
		$updateUser = mysql_query("
		UPDATE s_core_auth SET rights='$rights' WHERE id={$_GET["edit"]}
		");
	}
}
// Read rights
$getUserRights = mysql_query("
SELECT rights FROM s_core_auth WHERE id={$_GET["edit"]}
");
$rights = mysql_result($getUserRights,0,"rights");
$rights = unserialize($rights);

?>
<?php
// Read menu structure
$getMenu = "
SELECT * FROM s_core_menu WHERE parent=0 ORDER BY id ASC
";
$getMenu = mysql_query($getMenu);


?>
<form method="POST" id="rightForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]."&submitRights=1"?>">
<?php
while ($mainmenu = mysql_fetch_assoc($getMenu)){
?>
<ul>
	<li>
	<label style="width:50px"><?php echo $mainmenu["name"]?></label>
	<select name="<?php echo $mainmenu["id"] ?>[]" multiple style="width:120px">
	<?php
		$getSubMenu = mysql_query("
		SELECT * FROM s_core_menu WHERE parent={$mainmenu["id"]}
		");
		while ($submenu = mysql_fetch_assoc($getSubMenu)){
			?>
			<option value="<?php echo $submenu["id"] ?>" <?php echo $getSite["admin"] || in_array($submenu["id"],$rights) ? "selected" : ""?>><?php echo $submenu["name"]?></option>
			
			<?php
			// Query possible third layer
			$getSubSubMenu = mysql_query("
			SELECT * FROM s_core_menu WHERE parent={$submenu["id"]}
			");
			while ($subsubmenu = mysql_fetch_assoc($getSubSubMenu)){
				?>
					<option value="<?php echo $subsubmenu["id"] ?>" <?php echo $getSite["admin"] || in_array($subsubmenu["id"],$rights) ? "selected" : ""?>>..<?php echo $subsubmenu["name"]?></option>
				<?php
			}
		}
			
	?>
	</select>	 
	</li>

</ul>
<?php
}
?>
<div class="clear"/>
<div class="buttons" id="buttons">
	<ul>
		<li id="buttonTemplate" class="buttonTemplate">
		<button onClick="$('rightForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["auth"]["auth_new_save"] ?></div></button>
		</li>	
	</ul>
</div>
</form>
</fieldset>

<?php
}
?>
</body>
</html>

