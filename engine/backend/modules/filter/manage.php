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

?>
<?php
if ( $_GET["groupEdit"]){
	$translation = "propertygroup";
	$edit = $_GET["groupEdit"];
}elseif ($_GET["optionEdit"]){
	$translation = "propertyoption";
	$edit = $_GET["optionEdit"];
}

if (!empty($translation)) $sCore->sInitTranslations($edit,$translation);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="<?php echo $sLang["supplier"]["hersteller_hamann-media"] ?>"/>
<meta name="copyright" content="<?php echo $sLang["supplier"]["hersteller_2007_hamann"] ?>" />
<meta name="company" content="<?php echo $sLang["supplier"]["hersteller_eBusiness"] ?>" />
<meta name="reply-to" content="<?php echo $sLang["supplier"]["hersteller_eMail"] ?>" />
<meta name="rating" content="general" />
<meta http-equiv="content-language" content="de" />

<title>Supplier</title>

</head>
<body>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<?php
if ($_GET["save"]){
	if (empty($_POST["groupName"])){
		$sError = "Bitte geben Sie einen Gruppennamen ein";		
	}else {
		$groupname = mysql_real_escape_string(htmlspecialchars($_POST["groupName"]));
		$sortmode =intval($_POST["sortmode"]);
		// Insert group
		if ($_GET["groupEdit"]){
			$updateGroup = mysql_query("
			UPDATE s_filter
				SET 
					name = '$groupname',
					sortMode = '$sortmode',
					comparable = '{$_POST["groupComparable"]}'
				WHERE
					id = {$_GET["groupEdit"]}
			");
			$sInform = "Gruppe wurde aktualisiert";
		}else {
			
			$insertGroup = mysql_query("
			INSERT INTO s_filter (name, comparable, sortmode)
			VALUES ('$groupname','{$_POST["groupComparable"]}','$sortmode') 
			");
			$_GET["groupEdit"] = mysql_insert_id();
			$sInform = "Gruppe wurde angelegt";
		}
	}
}
if ($_GET["saveOption"]){
	if (empty($_POST["optionName"])){
		$sError = "Bitte geben Sie einen Optionsnamen ein";		
	}else {
		
		$groupname = mysql_real_escape_string(htmlspecialchars($_POST["optionName"]));
		// Insert group
		if ($_GET["optionEdit"]){
			$updateGroup = mysql_query("
			UPDATE s_filter_options
				SET 
					name = '$groupname',
					filterable = '{$_POST["optionComparable"]}'
				WHERE
					id = {$_GET["optionEdit"]}
			");
			$sInform = "Option wurde aktualisiert";
		}else {
			
			$insertGroup = mysql_query("
			INSERT INTO s_filter_options (name, filterable)
			VALUES ('$groupname','{$_POST["optionComparable"]}') 
			");
			$_GET["optionEdit"] = mysql_insert_id();
			$sInform = "Option wurde angelegt";
		}
	}
}
?>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteSupplier":
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
			echo "parent.TreeTest.reload();";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			//echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>


<?php
if ($_GET["groupEdit"]){
	$getGroup = mysql_query("
	SELECT * FROM s_filter WHERE id = {$_GET["groupEdit"]}
	");
	if (mysql_num_rows($getGroup)){
		$group = mysql_fetch_assoc($getGroup);
	}else {
		die ("Gruppe konnte nicht gefunden werden");
	}
}

if ($_GET["optionEdit"]){
	$getGroup = mysql_query("
	SELECT * FROM s_filter_options WHERE id = {$_GET["optionEdit"]}
	");
	if (mysql_num_rows($getGroup)){
		$option = mysql_fetch_assoc($getGroup);
	}else {
		die ("Option konnte nicht gefunden werden");
	}
}
?>
<?php
if ($_GET["groupNew"] || $_GET["groupEdit"]){
?>
<fieldset>
	<legend><?php echo $_GET["groupEdit"] ? "Gruppe bearbeiten" : "Neue Gruppe anlegen" ?></legend>
	<form name="save" enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>?groupEdit=<?php echo $_GET["groupEdit"] ?>&save=1">
	<ul>
	<li><label for="groupName">Gruppen-Bezeichnung:</label>
	<input name="groupName" type="text" id="groupName" class="w200" value="<?php echo $group["name"] ?>" />
	<?php
	if (!empty($_GET["groupEdit"])) echo $sCore->sBuildTranslation("groupName","groupName",$edit,$translation);
	?>
	</li>
	<li class="clear"/>
	<li>
		<label for="sortSelection">Sortierung:</label>
		 <select name="sortmode" id="sortmode" class="w100">
	     	  <option <?php echo empty($group["sortmode"]) ? 'selected="selected"' : "" ?> value="0">Alphabetisch</option>
		      <option <?php echo $group["sortmode"]==1 ? 'selected="selected"' : "" ?> value="1">Numerisch</option>
		      <option <?php echo $group["sortmode"]==2 ? 'selected="selected"' : "" ?> value="2">Anzahl</option>
	     </select>
	</li>
	<li class="clear"></li>
	</ul>
	<ul>
	<!--<li>
	<label for="img">Gruppe in Artikelvergleiche einbeziehen</label>
	<input type="checkbox" value="1" name="groupComparable" <?php echo $group["comparable"] || !empty($_GET["groupNew"]) ? "checked" : ""?> /></li>
	<li class="clear"></li>-->
	<li>
	<div class="buttons" id="div">
      <ul>
      	<li id="buttonTemplate" class="buttonTemplate">
        <button type="submit" value="send" class="button">
        <div class="buttonLabel">Speichern</div>
        </button>
       </li>
      </ul>
    </div>
     </li>
	</ul>
</form>
</fieldset>
<?php
}elseif ($_GET["optionNew"] || $_GET["optionEdit"]){
?>
<fieldset>
	<legend><?php echo $_GET["optionEdit"] ? "Option bearbeiten" : "Neue Option anlegen" ?></legend>
	<form name="save" enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>?optionEdit=<?php echo $_GET["optionEdit"] ?>&saveOption=1">
	<ul>
	<li><label for="optionName">Option-Bezeichnung:</label>
	<input name="optionName" type="text" id="optionName" class="w200" value="<?php echo $option["name"] ?>" />
	<?php
	if (!empty($_GET["optionEdit"])) echo $sCore->sBuildTranslation("optionName","optionName",$edit,$translation);
	?>
	</li>
	<li class="clear"/>
	</ul>
	<ul>
	<li>
	<label for="img">Option ist filterbar</label>
	<input type="checkbox" value="1" name="optionComparable" <?php echo $option["filterable"] || !empty($_GET["optionNew"]) ? "checked" : ""?> /></li>
	<li class="clear"></li>
	<li>
	<div class="buttons" id="div">
      <ul>
      	<li id="buttonTemplate" class="buttonTemplate">
        <button type="submit" value="send" class="button">
        <div class="buttonLabel">Speichern</div>
        </button>
       </li>
      </ul>
    </div>
     </li>
	</ul>
</form>
</fieldset>
<?php
}
?>
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>