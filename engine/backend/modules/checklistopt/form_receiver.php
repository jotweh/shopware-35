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
foreach ($_POST as $pKey => $pValue){
	if (!is_array($_POST[$pKey])){
		$_POST[$pKey] = htmlspecialchars(mysql_real_escape_string($pValue));
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>


<script>
function cAlphanumeric(length){
	pool = new Array ("0","1","2","3","4","5","6","7","8","9",
						"a","b","c","d","e","f","g","h","i","j","k","l","m",
						"n","o","p","q","r","s","t","u","v","w","x","y","z");
	i = 0;
	an = "";
	while (i < length)
	{
		i = i + 1;
		ze = Math.floor(36 * Math.random());
		ze = pool [ze];
		an = an + ze;
	}
	return an;

}
</script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons2.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<body>

<style type="text/css">
fieldset {
	position:relative; 
	margin: -25px 0px 0px;
}
label{
	text-align:left; 
	width:110px; 
	float:left;
}
label.forRadio{
	width:170px; 
}
.div_clear{
	clear:both; 
	height:3px;
}
</style>

<?php

//==========================================================================================
// Save POST
//==========================================================================================

if(isset($_POST['sAction']))
{
	foreach ($_POST as $postItemKey => $postItemVal) {
		$conf = explode("_", $postItemKey);
		$cmsSupportId = $conf[1];
		$eMail = $postItemVal;
		
		mysql_query("
			UPDATE `s_cms_support` 
			SET `email` = '{$postItemVal}' 
			WHERE `id` = '{$cmsSupportId}' 
			LIMIT 1
		");
	}
	?>
	<script type="text/javascript">
		parent.parent.parent.Growl("eMail-Adressen der Formulare wurden aktualisiert!");
	</script>
	<?php
}


//==========================================================================================
// Load values
//==========================================================================================

$getSupportEmailsQ = mysql_query("
	SELECT `id` , `name` , `email`
	FROM `s_cms_support`
");

if(mysql_num_rows($getSupportEmailsQ) == 0) die("Es sind keine Formulare vorhanden!");
?>
<form id="sForm" method="POST">
<fieldset>
	<legend>Empfänger eMail-Adressen der Fomulare</legend>
	
	<div style="margin-bottom:30px;">
		<p style="float:left;font-size:11px;width:119px;"><b>Formular</b></p>
		<p style="float:left;font-size:11px;"><b>Empfänger-eMail</b></p>
	</div>
	<div class='div_clear'></div>
	
	<?php
	while ($support = mysql_fetch_array($getSupportEmailsQ)) {
		echo sprintf("<label>%s:</label>", $support['name']);
		echo sprintf("<input name='sEmail_%d' type='text' value='%s' style='width:200px'/>", $support['id'], htmlentities($support['email']));
		echo "<div class='div_clear'></div>";
	}	
	?>
	
	<input type="hidden" name="sAction" />
	
</fieldset>
</form>

<div class="buttons" onclick="$('sForm').submit();" id="sSave2" style="margin-top:20px;display:block" id="buttons"><ul><li class="buttonTemplate">
<button  id="sSave" name="action" type="submit" value="save" class="button"><div class="buttonLabel">Speichern</div></button>
</div>

</body>
</html>