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
if (!empty($_GET["change"]))
{
	$change = $_GET["change"];
	if ($change=="-1") $change = "0";
	$change = mysql_real_escape_string($change);
	$updateTemplate = mysql_query("
	UPDATE s_core_config
		SET value = 'templates/$change'
	WHERE
		name = 'sTEMPLATEPATH'
	");
	
	$updateShop = mysql_query("
	UPDATE s_core_multilanguage SET template = 'templates/$change'
	WHERE `default` = 1
	");
	
	$sCore->sCONFIG['sTEMPLATEPATH'] = "templates/$change";
	$sInform = $sLang["templates"]["templates_template_changed"];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Reorder TreePanel</title>
<script type="text/javascript" src="lightbox/mootools.js"></script>
<script type="text/javascript" src="lightbox/lightbox.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="lightbox/lightbox.css" rel="stylesheet" type="text/css" />
<!-- Common Styles for the examples -->
</head>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "changeTemplate":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?change="+sId;
			break;
	}
}

function changeTemplate(ev,text,number){	
	parent.parent.sConfirmationObj.show('<?php echo $sLang["templates"]["templates_template_changed"] ?> "'+text+'" <?php echo $sLang["templates"]["templates_set_active"] ?>',window,'changeTemplate',ev);	
}
</script>

<body>


<?php
if ($sCore->sCONFIG['sHIDETEMPLATES']){
?>
<fieldset class="col2_cat2">
<legend style="font-weight:bold;"><a class="ico exclamation"></a> <?php echo $sLang["templates"]["templates_tip"] ?></legend>
<?php echo $sLang["templates"]["templates_solid_template"] ?>
</fieldset>
<?php
}else {
?>
<fieldset class="col2_cat2">
<legend style="font-weight:bold;"><a class="ico help"></a> <?php echo $sLang["templates"]["templates_tips"] ?></legend>

<div style="float:left; width: 45%">
<strong><?php echo $sLang["templates"]["templates_select_template"] ?></strong><br />
<?php echo $sLang["templates"]["templates_every_template"] ?>
<a class="ico3 world_link" style="margin-top:5px;font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:140px;" href="http://www.hamann-media.de/dev/wiki/Template_%C3%9Cbersicht" target="_blank"><?php echo $sLang["templates"]["templates_more_informations"] ?></a>
</div>
<div style="float:left; width: 45%; border-left: 1px solid #dfdfdf; padding-left: 15px;">
<?php echo $sLang["templates"]["templates_click_on"] ?>
<?php echo $sLang["templates"]["templates_click_on_end"] ?>
	<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="parent.loadSkeleton('templatespreview',false, {'template':'-1'});" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["templates"]["templates_preview_end"] ?></div></button>
			</li>	
		</ul>
	</div>
	
<br /><?php echo $sLang["templates"]["templates_please_note"] ?>
</div>
</fieldset>
<?php

$path = '../../../../templates/*';
$dirs = glob($path, GLOB_ONLYDIR);
natsort($dirs);

$templates = array();

foreach ($dirs as $dir) {
	$file = basename($dir);
	if(in_array($file, array('.svn'))||strpos($file, '_')===0) continue;
	$templates[$file] = $file;
}

foreach ($templates as $templateKey => $templateValue){
	if (is_file('../../../../templates/'.$templateValue.'/preview.png')){
		$templates[$templateKey] = '../../../../templates/'.$templateValue.'/preview.png';
		$templatesPreview[$templateKey] = '../../../../templates/'.$templateValue.'/preview_thb.png';
	}else {
		$templates[$templateKey] = false;
	}
}
?>


<fieldset class="grey">
<legend><?php echo $sLang["templates"]["templates_template_selection"] ?></legend>

	<ul class="images" style="width:100%">
	
<?php
	foreach ($templates as $templateKey => $templateValue){
		if ($sCore->sCONFIG['sTEMPLATEPATH']=="templates/$templateKey"){
			$main = true;
		}else {
			$main = false;
		}
		
		if (is_file("../../../../templates/$templateKey/info.txt")){
			
			unset($info);
			$fp = fopen("../../../../templates/$templateKey/info.txt","r");
			while ($buffer = fread($fp,1024)) $info.=$buffer;
			fclose($fp);
			
		}else {
			unset($info);
		}
?>	
	<li style="width:200px;height:150px;margin-bottom:55px" id="thumb" <?php if ($main) { ?> class="main" <?php } ?>>
		<div style="width:150px;height:100px"><?php if ($main) { ?><div class="first" style="z-index:1000;"><?php echo $sLang["templates"]["templates_selection"] ?></div><?php } ?><a rel="lightbox[photos]" href="<?php echo $templateValue ?>" title="Template: /templates/<?php echo $templateKey ?><br /><?php echo $info ?>"><img  class='toolTip' title="<?php echo $info ?>" src="<?php echo $templatesPreview[$templateKey] ?>" style="max-height:100px;" border=0 /></a></div>
		Template: /templates/<?php echo $templateKey ?>
		<?php	
		if (empty($main)){
		?>
			<a onClick="parent.loadSkeleton('templatespreview',false, {'template':'<?php echo $templateKey ?>'});" class="ico3 image" style="font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:91px;cursor:pointer"> <?php echo $sLang["templates"]["templates_preview"] ?></a>
			<a onClick="changeTemplate('<?php echo $templateKey ? $templateKey : "-1" ?>','/templates/<?php echo $templateKey ?>',1)" class="ico3 accept" style="font-size:10px;padding: 2px 0 2px 25px; display: block; background-position: 0 2px;width:91px;cursor:pointer"> <?php echo $sLang["templates"]["templates_select"] ?></a>
		<?php
		}else {
		?>
			
		<?php
		}
		?>
	</li>
<?php
	}
?>
	</ul>
</fieldset>
<div class="clear"></div><br/>
<!-- ausgabe ende -->

<script language="javascript">
/* <![CDATA[ */
window.addEvent('domready',function(){
	var myTips = new Tips($$('.toolTip'));
	Lightbox.init({descriptions: '.lightboxDesc', showControls: true});
	
	<?php
	if ($sInform){
		echo "parent.Growl('$sInform');";
	}
	if ($sError){
		echo "parent.Growl('$sError');";
		echo "parent.sWindows.focus.shake(50);";
	}
	?>
	
});
/* ]]> */
</script>
<?php
}
?>
</body>
</html>
