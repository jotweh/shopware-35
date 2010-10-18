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
	$hash = $_SERVER["HTTP_HOST"]."-";
	$update = mysql_query("
	UPDATE s_core_config SET value='$hash".passwortgenerator(30)."' WHERE name='sAPI'
	");
}
function passwortgenerator($number){
	$zeichen = "qwertzupasdfghkyxcvbnm";
	$zeichen .= "123456789";
	$zeichen .= "WERTZUPLKJHGFDSAYXCVBNM";

	srand((double)microtime()*1000000); 
  	  //Startwert für den Zufallsgenerator festlegen

	for($i = 0; $i < $number; $i++)
	{
	  $password .= substr($zeichen,(rand()%(strlen ($zeichen))), 1);
	}
	
	return $password;
}
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




<fieldset class="col2_cat2" style="margin-top:-26px;">
<legend><?php echo $sLang["presettings"]["api_shopware_api_Access"] ?></legend>
<?php echo $sLang["presettings"]["api_with_the_shopware_api"] ?>
Informationen und Hilfestellung zur API erhalten Sie im Entwickler Portal.<br/>



	<div class="buttons" id="buttons">
		<ul>
		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="http://www.hamann-media.de/dev" class="bt_icon information_frame" target="_blank" style="text-decoration:none;"><?php echo $sLang["presettings"]["api_shopware_developer_portal"] ?></a></li>	
		
		</ul>
		</div>

</fieldset>
<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?sSave=1&id=".$_GET["id"] ?>">

<?php

$sql = "
SELECT name, value, description FROM s_core_config WHERE `name`='sAPI'
";

$queryOptions = @mysql_query($sql);
$api = @mysql_fetch_array($queryOptions);
?>

<fieldset style="margin-top:-15px;">
<legend><?php echo $sLang["presettings"]["api_api_key"] ?></legend>
<ul>
<li>
<label><?php echo $sLang["presettings"]["api_api_key_1"] ?></label>
<input class="w200" style="height:25px;width:280px" maxlength="40" value="<?php echo $api["value"]?>" name="sAPI">
</li>
<li class="clear"></li>

	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["api_Generate_new_key"] ?></div></button></li>	

		
		</ul>
		</div>
</ul>

</fieldset>


<div class="clear"></div>

</form>






</body>

</html>