<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../../");
include("../../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
echo "FAIL";
	die();
}

$_POST['json'] = stripslashes(str_replace("\"","",$_POST['json']));
?>
{
	"init": {
		"title": "<?php echo $_POST['json']?>",
		"minwidth": "640",
		"minheight": "480",
		"content": "",
		"loader": "extern",
		"url": "<?php echo $_POST['json']?>"
	}
	
}
