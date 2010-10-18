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

?>
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

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
<body style="padding: 10 10 10 10; margin: 0 0 0 0;font-size:13px ">


	<fieldset style="margin-top:-27px;">
	<legend>Hinweis</legend>
	<p>Bitte beachten Sie die Hinweise in der Dokumentation der Einstellungen, bevor Sie Änderungen vornehmen!</p>
	
		<div class="buttons" id="buttons">
		<ul>	
			
		<li id="buttonTemplate" class="buttonTemplate"><a class="bt_icon question_frame" target="_blank" href="http://www.hamann-media.de/dev/wiki/Hilfe:Einstellungen" value="send" style="text-decoration:none;">Hilfe für Grundeinstellungen öffnen</a></li>	
		
		</ul>
		</div>
	
	</fieldset>


	


</body>

</html>