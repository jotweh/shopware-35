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
<!DOCTYPE html>
<html>
<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>

<style type="text/css">
body { padding: 10px; }
.clear { /*  - fixfloat */
	clear: both;
	padding: 0;
	margin:0;
	width: 0px;
	height: 6px;
	line-height: 0px;
	font-size: 0px;
}
.error, .notice, .success {padding:.8em;margin:0 .5em 2em; border:2px solid #ddd;}
.error, .instyle_error, input.instyle_error {background:#FBE3E4;color:#8a1f11;border-color:#FBC2C4;}
.notice {background:#FFF6BF;color:#514721;border-color:#FFD324;}
.success, .instyle_success {background:#E6EFC2;color:#264409;border-color:#C6D880;}
.error a {color:#8a1f11;}
.notice a {color:#514721;}
.success a {color:#264409;}
h2 { margin: 0 0 .5em; }
p { margin: 0 0 1.5em }
.logo { margin: 1em; }
.logo img { display: block; width: 300px; height: 140px; margin: 0 auto }
.info { font-size: 11px; }
.register .button { display: inline-block; width: 129px; height: 27px; border-left: 1px solid #666; padding: 0 10px 0 11px; color: #333; text-decoration: none; position: relative; font-weight: 700; -webkit-user-select: none; -moz-user-select: none; user-select: none; font-size: 10px; }
.register .button:active { top: 1px; }
</style>
<body>
<?php
$_GET["id"] = 54;
if ($_GET["sSave"]){
	
	// Check Trusted ID
	if ((strlen($_POST["sTSID"])!=33 || substr($_POST["sTSID"],0,1)!="X") && $_POST["sTSID"]!=""){
		echo '<div class="error">'.$sLang["presettings"]["trusted_please_enter_trusted-shop-id"].'</div>';
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

<div class="logo">
	<img src="ts.gif" alt="Trusted Shops Logo">
</div>

<div class="info">
	<h2>Gütesiegel und Käuferschutz</h2>
	<p>
		Trusted Shops ist das bekannte Internet-Gütesiegel für Online-Shops mit Käuferschutz für IhreKunden. Bei einer Zertifizierung wird Ihr Shop umfassenden Tests unterzogen. Diese Prüfung mit mehr als 100 Einzelkriterien orientiert sich an den Forderungen von Verbraucherschützern sowie dem nationalen und europäischen Recht.
	</p>
	
	
	<h2>Mehr Umsatz durch mehr Vertrauen!</h2>
	<p>
		Das Trusted Shops Gütesiegel ist optimal, um das Vertrauen Ihrer Online-Kunden zu steigern. Vertrauen steigert die Bereitschaft Ihrer Kunden, bei Ihnen einzukaufen.
	</p>
	
	<h2>Weniger Kaufabbrüche</h2>
	<p>
		Sie bieten Ihren Online-Kunden ein starkes Argument: Den Trusted Shops Käuferschutz. Durch diese zusätzliche Sicherheit werden weniger Einkäufe abgebrochen.
	</p>
	
	<h2>Ertragreiche und nachhaltige Kundenbeziehung</h2>
	<p>
		Das Trusted Shops Gütesiegel mit Käuferschutz ist für viele Online-Shopper ein nachhaltiges Qualitätsmerkmal für sicheres Einkaufen im Web. Aus Einmalkäufern werden Stammkunden.
	</p>
	<div class="register">
		<a class="button" href="http://www.trustedshops.de/shopbetreiber/index.html?et_cid=14&et_lid=29818" target="_blank">
			Informieren und anmelden!
		</a>
	</div>
</div>

</fieldset>


<?php
include("../../../backend/elements/window/translations.htm");
?>

<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>

</html>