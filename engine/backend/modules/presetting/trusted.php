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
$_GET["id"] = 54;
if ($_GET["sSave"]){
	
	// Check Trusted ID
	if ((strlen($_POST["sTSID"])!=33 || substr($_POST["sTSID"],0,1)!="X") && $_POST["sTSID"]!=""){
		echo "<p style=\"font-weight:bold;color:#F00;font-size:16px\">".$sLang["presettings"]["trusted_please_enter_trusted-shop-id"]."</p><br />";
	}else {
	
		foreach ($_POST as $key => $value){
			$update = mysql_query("
			UPDATE s_core_config SET value='$value' WHERE name='$key'
			");
		}
	
	}
}

$sCore->sInitTranslations(1,"config");

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
	height: 6px;
	line-height: 0px;
	font-size: 0px;
}
</style>
<body style="padding: 10 10 10 10; margin: 0 0 0 0; ">


<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sSave=1&id=".$_GET["id"] ?>">
<fieldset style="margin-top:-27px;height:100px;float:none;margin-bottom:0;">
<?php



$sql = "
	SELECT name, value, multilanguage  FROM s_core_config WHERE `name`='sTSID' 
	";

	$queryOptions = mysql_query($sql);
?>


<legend><?php echo $sLang["presettings"]["trusted_please_enter_yout_trusted-shops-id"] ?></legend>

<?php
while ($field=mysql_fetch_array($queryOptions)){
?>
<ul>
<li>
<label><?php echo $sLang["presettings"]["trusted_trusted-shops-id"] ?></label>
<input class="w200" style="height:25px;width:280px" maxlength="40" value="<?php echo $field["value"]?>" name="<?php echo $field["name"]?>" id="<?php echo $field["name"]?>">
</li>
<li class="clear"></li>
</ul>
<?php

	if ($field["multilanguage"]){
		echo $sCore->sBuildTranslation($field["name"],$field["name"],1,"config");
	}
}
?>

<?php

?>

	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["trusted_save"] ?></div></button></li>	
		
		</ul>
		</div>



</fieldset>
</form>


<div class="clear"></div>

<fieldset class="white" style="margin-top:-15px;">
<legend><?php echo $sLang["presettings"]["trusted_Your_store_with_Trusted_Shop_seal"] ?></legend>

<img src="ts.gif" style="float:left;margin-right:15px;">
<p>
<?php echo $sLang["presettings"]["trusted_Trusted_Shop_is_the_hallmark_for_online-shops"] ?>
</p>
<strong>
<?php echo $sLang["presettings"]["trusted_Your_Benefits_from_trusted_shop"] ?><br />
</strong><br />
<?php echo $sLang["presettings"]["trusted_Improve_your_shop_and_your_ordering_process"] ?>
<br /><strong>
<?php echo $sLang["presettings"]["trusted_What_does_your_Trusted_Shop"] ?>
</strong>
<br /><br />
<?php echo $sLang["presettings"]["trusted_Certification_of_your_online_store"] ?>

<br />
<?php echo $sLang["presettings"]["trusted_The_Trusted_Shops_effect"] ?></strong><br />
<?php echo $sLang["presettings"]["trusted_The_combination_of_audit"] ?>
<br />
<a href="http://www.trustedshops.de/shopbetreiber/shoploesungen/shopware.html" target="_blank"><?php echo $sLang["presettings"]["trusted_more_informations"] ?></a>

</fieldset>




<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>