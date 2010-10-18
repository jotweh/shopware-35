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
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo $sLang["presettings"]["pricegroup_Reorder_TreePanel"] ?></title>
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/yui/yui-utilities.js"></script>
<script type="text/javascript" src="../../../vendor/ext/adapter/yui/ext-yui-adapter.js"></script>     <!-- ENDLIBS -->
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="tree.js"></script>
<script language="javascript">
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "saveArticle":
		var frame = window.frames['settingsList'].document;
		frame.getElementById('ourForm').submit();
	
		parent.Growl('<?php echo $sLang["presettings"]["pricegroup_settings_saved"] ?>');
		break;
	}
}

window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
};
</script>
<style>
.x-panel-body {
border-width:0;
}
.x-panel-body-noheader, .x-panel-mc .x-panel-body {
border-width:0;
}


</style>
<!-- Common Styles for the examples -->
</head>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<body id="case1">
<div id="tree-div" class="col1"></div>
<fieldset class="col2">
<iframe id="settingsList" name="settingsList" src="" border=0 style="border:0px" frameborder="0" width="90%" scrolling="auto"></iframe>
</fieldset>
<div class="clear"></div>
</body>
</html>
