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
if ($_GET["sSave"]){
	foreach ($_POST as $key => $value){
		if (preg_match("/Conf/",$key)) continue;
		$value = mysql_real_escape_string($value);
		$update = mysql_query("
		UPDATE s_core_config SET value='$value' WHERE name='$key'
		");
	}
}

$sCore->sInitTranslations(1,"config");

if(empty($_REQUEST['id']))
	die("missing param: id");
?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>

<style>
.clear { /*  - fixfloat */
	clear: both;
	padding: 0;
	margin:0;
	width: 0px;
	height: 0px;
	line-height: 0px;
	font-size: 0px;
}
</style>
<body style="padding: 10 10 10 10; margin: 0 0 0 0; ">
<?php
$sCore->sInitTranslations("1","config");
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sSave=1&id=".$_GET["id"] ?>">

<?php
$_GET["id"] = intval($_GET["id"]);
$queryGroups = mysql_query("
SELECT * FROM s_core_config_groups WHERE id={$_GET["id"]}
");
if (!@mysql_num_rows($queryGroups)) die();
$group = mysql_fetch_array($queryGroups);

$sql = "
SELECT * FROM s_core_config WHERE `group`={$group["id"]} 
";

$queryOptions = mysql_query($sql);
if (!@mysql_num_rows($queryOptions))  exit;
?>

<fieldset style="margin-top:-29px"><legend><?php echo $group["name"]?></legend>

<fieldset class="col2_cat2" style="margin-top:-15px">
	<legend><a class="ico exclamation"></a><span style=\"color:#F00\">Hinweis:</span></legend>
	
	<strong>
	<p style="color:#F00;font-weigt:bold;font-size:11px">
	<?php if($_GET['id'] == 72) { ?>
	Achtung! Sie dürfen die Funktion "Automatische Erinnerung zur Artikelbewertung" nur im Rahmen Ihrer Trusted-Shops Zertifizierung verwenden, wenn Sie das Einverständnis Ihrer Kunden haben!<br/><br/>
	<?php } ?>
	Prüfen Sie eventuell vorhandene wichtige Informationen und Hinweise in unserem Wiki, bevor Sie Einstellungen verändern!
	</strong>
	</p>
	<a class="ico3 world" style="width:120px" href="http://www.shopware.de/wiki/" target="_blank"><span style="margin-left:5px">Zum Wiki...</span></a>
	</fieldset>
<?php echo str_replace(array("../../connectors","clickandbuy_logo1.gif"),array("../../../connectors","logo.png"),$group["description"])?>

<?php
while ($field=mysql_fetch_array($queryOptions)){
?>
<ul>
<li>
<label><?php echo $field["description"]?>:</label>
<?php
if ($field["fieldtype"]=="int"){
?>
<select class="w200" style="height:25px" name="<?php echo $field["name"]?>" id="<?php echo $field["name"]?>">
<option value="0" <?php if ($field["value"]=="0" || empty($field["value"])){echo "selected";}?>>Nein</option>
<option value="1" <?php if ($field["value"]=="1"){echo "selected";}?>>Ja</option>
</select>
<?php
}elseif ($field["fieldtype"]=="textarea"){
?>
<textarea class="w200" style="height:125px;width:200px" rows=12 cols=8 name="<?php echo $field["name"]?>" id="<?php echo $field["name"]?>"><?php echo htmlentities($field["value"]);?></textarea>
<?php
}
else {
?>
<input class="w200" style="height:25px" value="<?php echo htmlentities($field["value"]);?>" name="<?php echo $field["name"]?>" id="<?php echo $field["name"]?>">
<?php
}
if ($field["multilanguage"]){
echo $sCore->sBuildTranslation($field["name"],$field["name"],1,"config");
}
?>
</li>

</ul>
<?php
}
?>
<ul>
<li class="clear"></li>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["pricegroup_save"] ?></div></button>
	</li></ul></div>
</li>
</ul>

</fieldset>
<?php

?>

<div class="clear"></div>

</form>





<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>