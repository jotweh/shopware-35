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
	parent.parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}

if (!$_GET["article"]) die("No Article");

$sCore->sInitTranslations($_GET["article"],"properties");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="de" />
<title>Links</title>
</head>
<body>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<script>

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



<fieldset style="margin-top: -20px;">
<legend><a class="ico help"></a> Hinweis</legend>
<strong>Sie können diese Funktion erst nutzen, wenn Sie eine Eigenschaftsgruppe in den Artikel-Stammdaten ausgewählt haben.</strong><br/>
Die Hinterlegung der Artikeleigenschaften ermöglicht den Vergleich verschiedener Artikel in der Storefront
und die gezielte Filterung innerhalb der Kategorien nach z.B. Farbe oder Beschaffenheit eines Artikels.
</fieldset>
<?php
$selectGroup = mysql_query("SELECT filtergroupID FROM s_articles WHERE id = {$_GET["article"]}");
$selectGroup = mysql_result($selectGroup,0,"filtergroupID");
if ($selectGroup){
	$selectGroup = mysql_query("
	SELECT * FROM s_filter WHERE id = $selectGroup
	");
	
	$selectGroup = mysql_fetch_assoc($selectGroup);
}else {
	die("Noch keine Gruppe ausgewählt!");
}

if ($_GET["send"]){
	$deletePrevious = mysql_query("
		DELETE FROM s_filter_values WHERE 
		articleID = {$_GET["article"]}
	");
	foreach ($_POST["sFilterValues"] as $groupID => $option)
	{
		foreach ($option as $key => $values)
		{
			foreach (explode('|', $values) as $value)
			{
				if ($key && $groupID && $value)
				{
					$value = trim($value);
					$value = mysql_real_escape_string($value);
					$insertValue = mysql_query("
						INSERT INTO s_filter_values (groupID,optionID,articleID,value)
						VALUES ($groupID,$key,{$_GET["article"]},'$value') 
					");
				}
			}
		}
	}
}
?>
<fieldset>
<legend>Gruppe: <?php echo $selectGroup["name"] ?></legend>

	<form method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]?>&send=1">
		<ul>
	<?php
	// Read all assigned properties
	$sql = "
	SELECT fo.name, fo.id, GROUP_CONCAT(value ORDER BY value SEPARATOR '|') as value
	FROM s_filter_relations AS fr, s_filter_options AS fo
	LEFT JOIN s_filter_values AS fv ON fv.groupID = {$selectGroup["id"]}
	AND fv.optionID = fo.id AND fv.articleID = {$_GET["article"]}
	WHERE fr.groupID = {$selectGroup["id"]}
	AND fr.optionID = fo.id
	GROUP BY fo.id
	";
	
	$getProperties = mysql_query($sql);
	while ($property = mysql_fetch_assoc($getProperties)){
	?>
		<li><label><?php echo $property["name"] ?>:</label>
	
		<input name="sFilterValues[<?php echo $selectGroup["id"] ?>][<?php echo $property["id"] ?>]" type="text" id="<?php echo $property["id"] ?>" class="w200" value="<?php echo $property["value"] ?>" />
		<?php
		echo $sCore->sBuildTranslation($property["id"],$property["id"],$_GET["article"],"properties");
		?>
		</li>
		<li class="clear"></li>
	<?php
	}
	?>
	</ul>
	<ul>
		<li>
		<div class="buttons" id="buttons" style="float:left;">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
				<button type="submit" value="send" class="button"><div class="buttonLabel">Speichern</div></button>
				</li>	
			</ul>
		</div>  
		</li>
		<li class="clear"/>
	</ul>
	</form>	
<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</fieldset>			
</body>
</html>