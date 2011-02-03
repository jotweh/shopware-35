<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo "FAIL";
	die();
}

if(!empty($_REQUEST['artID']))
	$values['artID'] = intval($_REQUEST['artID']);


if(!empty($_REQUEST['action'])) {
if($_REQUEST['action']=="save")
{
	if(!empty($_REQUEST['field_name_new']))
		$values['field_name_new'] = mysql_real_escape_string($_REQUEST['field_name_new']);
	if(!empty($_REQUEST['field_label_new']))
		$values['field_label_new'] = mysql_real_escape_string($_REQUEST['field_label_new']);
	
	
	
	if (count($_REQUEST['field'])) { foreach ($_REQUEST['field'] as $key => $value)
	{
		if(!empty($value['name']))
			$values['field'][$key]['name'] = mysql_real_escape_string($value['name']);
		if(!empty($value['label']))
			$values['field'][$key]['label'] = mysql_real_escape_string($value['label']);
		if(!empty($value['typ']))
			$values['field'][$key]['typ'] = mysql_real_escape_string($value['typ']);
		if(!empty($value['class']))
			$values['field'][$key]['class'] = mysql_real_escape_string($value['class']);
		if(!empty($value['note']))
			$values['field'][$key]['note'] = mysql_real_escape_string($value['note']);
		if(!empty($value['error_msg']))
			$values['field'][$key]['error_msg'] = mysql_real_escape_string($value['error_msg']);
		if(!empty($value['required']))
			$values['field'][$key]['required'] = mysql_real_escape_string($value['required']);
		if(!empty($value['value']))
			$values['field'][$key]['value'] = mysql_real_escape_string($value['value']);
		if(!empty($value['position']))
			$values['field'][$key]['position'] = mysql_real_escape_string($value['position']);
			
		if (!$values['field'][$key]['position']) $values['field'][$key]['position'] = "0";
	}}
		
	if(!empty($_REQUEST['artID']))
		$values['artID'] = intval($_REQUEST['artID']);
	if(!empty($_REQUEST['text']))
		$values['text'] = mysql_real_escape_string($_REQUEST['text']);
	else 
		$values['text'] = "";
	if(!empty($_REQUEST['text2']))
		$values['text2'] = mysql_real_escape_string($_REQUEST['text2']);
	else 
		$values['text2'] = "";
	if(!empty($_REQUEST['name']))
		$values['name'] = mysql_real_escape_string($_REQUEST['name']);
	else 
		$values['name'] = "";
	if(!empty($_REQUEST['email']))
		$values['email'] = mysql_real_escape_string($_REQUEST['email']);
	else 
		$values['email'] = "";
	if(!empty($_REQUEST['email_template']))
		$values['email_template'] = mysql_real_escape_string($_REQUEST['email_template']);
	else 
		$values['email_template'] = "";
	if(!empty($_REQUEST['email_subject']))
		$values['email_subject'] = mysql_real_escape_string($_REQUEST['email_subject']);
	else 
		$values['email_subject'] = "";
	if(!empty($_REQUEST['value']))
		$values['value'] = mysql_real_escape_string($_REQUEST['value']);
	else 
		$values['value'] = "";
	if(!empty($_REQUEST['ticket_task_name']))
		$values['ticket_task_name'] = mysql_real_escape_string($_REQUEST['ticket_task_name']);
	else 
		$values['ticket_task_message'] = 0;
	if(!empty($_REQUEST['ticket_task_message']))
		$values['ticket_task_message'] = mysql_real_escape_string($_REQUEST['ticket_task_message']);
	else 
		$values['ticket_task_message'] = 0;
	if(!empty($_REQUEST['ticket_task_email']))
		$values['ticket_task_email'] = mysql_real_escape_string($_REQUEST['ticket_task_email']);
	else 
		$values['ticket_task_email'] = 0;
	if(!empty($_REQUEST['ticket_task_subject']))
		$values['ticket_task_subject'] = mysql_real_escape_string($_REQUEST['ticket_task_subject']);
	else 
		$values['ticket_task_subject'] = 0;

	if(!isset($values['artID']))
	{
		if (empty($values['name'])){
			echo "<br /><br /><strong>Füllen Sie das Feld Name aus</strong><br /><br /><br /><br />";
		}else {
		$sql = "
			INSERT INTO 
				`s_cms_support`
			( 	
				`name` , `text` , `text2` , `email` , `email_template` , `email_subject`
			)
			VALUES 
			(
				'{$values['name']}', '{$values['text']}', '{$values['text2']}', '{$values['email']}', '{$values['email_template']}', '{$values['email_subject']}'
			);
		";
		$result = mysql_query($sql);
		$values['artID'] = mysql_insert_id();
		}
	}
	else
	{
		if (empty($values['name'])){
			echo "<strong>Füllen Sie das Feld Name aus</strong><br /><br /><br /><br />";
		}else {
		$sql = "
			UPDATE `s_cms_support` 
			SET 
				`name` = '{$values['name']}',
				`text` = '{$values['text']}',
				`text2` = '{$values['text2']}',
				`email` = '{$values['email']}',
				`email_template` = '{$values['email_template']}',
				`email_subject` = '{$values['email_subject']}'
			WHERE 
				`id` = '{$values['artID']}'
		";
		$result = mysql_query($sql);
		}
	}
	if (!empty($values['field_name_new']))
	{
		$value['name'] = $values['field_name_new'];
		$value['label'] = $values['field_label_new'];
		$sql = "
			INSERT INTO 
				`s_cms_support_fields`
			( 	
				`name` , `label`, `supportID`, `added`
			)
			VALUES 
			(
				'{$value['name']}', '{$value['label']}', '{$values['artID']}', NOW()
			);
		";
		$result = mysql_query($sql);
		$values['field'][mysql_insert_id()] = $value;
	}
	if(!empty($_REQUEST['text']))
		$values['text'] = stripslashes($_REQUEST['text']);
	if(!empty($_REQUEST['text2']))
		$values['text2'] = stripslashes($_REQUEST['text2']);
	if(!empty($_REQUEST['name']))
		$values['name'] = stripslashes($_REQUEST['name']);
	if(!empty($_REQUEST['email']))
		$values['email'] = stripslashes($_REQUEST['email']);
	if(!empty($_REQUEST['email_template']))
		$values['email_template'] = stripslashes($_REQUEST['email_template']);	
	if(!empty($_REQUEST['email_subject']))
		$values['email_subject'] = stripslashes($_REQUEST['email_subject']);
	if(!empty($_REQUEST['value']))
		$values['value'] = stripslashes($_REQUEST['value']);
	
	if (count($values['field'])) { foreach ($values['field'] as $key => $value)
	{
		
		$sql = "
			UPDATE `s_cms_support_fields` 
			SET 
				`name` = '{$value['name']}',
				`note` = '{$value['note']}',
				`typ` = '{$value['typ']}',
				`required` = '{$value['required']}',
				`label` = '{$value['label']}',
				`class` = '{$value['class']}',
				`value` = '{$value['value']}',
				`error_msg` = '{$value['error_msg']}',
				`position` = '{$value['position']}'
			WHERE 
				`id` = '{$key}'
		";
		$result = mysql_query($sql);
		
		if(!empty($value['name']))
			$values['field'][$key]['name'] = stripslashes($value['name']);
		if(!empty($value['note']))
			$values['field'][$key]['note'] = stripslashes($value['note']);
		if(!empty($value['typ']))
			$values['field'][$key]['typ'] = stripslashes($value['typ']);
		if(!empty($value['required']))
			$values['field'][$key]['required'] = stripslashes($value['required']);
		if(!empty($value['label']))
			$values['field'][$key]['label'] = stripslashes($value['label']);
		if(!empty($value['class']))
			$values['field'][$key]['class'] = stripslashes($value['class']);
		if(!empty($value['value']))
			$values['field'][$key]['value'] = stripslashes($value['value']);
		if(!empty($value['error_msg']))
			$values['field'][$key]['error_msg'] = stripslashes($value['error_msg']);
					
	}}
}
elseif($_REQUEST['action']=="del"&&!empty($_REQUEST['artID']))
{
	$sql = "DELETE FROM `s_cms_support` WHERE `id` = '{$_REQUEST['artID']}' LIMIT 1;";
	$result = mysql_query($sql);
	$sql = "DELETE FROM `s_cms_support_fields` WHERE `supportID` = '{$_REQUEST['artID']}'";
	$result = mysql_query($sql);
	header("Location: detailsCat.php");
	exit();
	
}
elseif($_REQUEST['action']=="delField"&&!empty($_REQUEST['artID'])&&!empty($_REQUEST['fieldID']))
{
	$sql = "DELETE FROM `s_cms_support_fields` WHERE `id` = '{$_REQUEST['fieldID']}' LIMIT 1;";
	$result = mysql_query($sql);	
}
}

if (!empty($_REQUEST['artID']))
{


	$sql = "
		SELECT 
			 `id`, `name` , `text` , `text2` , `email` , `email_template` , `email_subject` 
		FROM 
			`s_cms_support`
		WHERE `id`='".intval($_REQUEST['artID'])."' ";
	$result = mysql_query($sql);
	$values = mysql_fetch_assoc($result);
	$values['artID'] = $values['id'];
	
	$sql = "
		SELECT 
			`id`, `error_msg` , `name` , `note` , `typ` , `required` , `label` , `class` , `value`, `position`
		FROM 
			`s_cms_support_fields`
		WHERE 
			`supportID`='{$values['artID']}' ORDER BY `added`";
	$result = mysql_query($sql);
	while($tooltip = mysql_fetch_assoc($result))
	{
		$values['field'][$tooltip['id']] = $tooltip;
	}
}

?>
<html>
<head>
<title><?php echo $sLang["support"]["detailsart_help_administration"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript"> 
	tinyMCE.init({
		// General options
		mode: "exact",
		elements : "text,test",
		theme : "advanced",
		<?php echo$sCore->sCONFIG['sTINYMCEOPTIONS']?>, 
		//cleanup : false, skin : "o2k7", relative_urls : false,theme_advanced_resizing : true, theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left",	theme_advanced_path_location : "bottom",
		plugins : "safari,pagebreak,style,layer,table,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,|,insertdate,inserttime,preview,|,forecolor,backcolor"	
	});
	
	window.addEvent('domready',function(){
		var myTips = new Tips($$('.toolTip'));
	});
</script>
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteField":
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?artID=<?php echo$values['artID']?>&action=delField&fieldID="+sId;
			break;
		case "deleteForm":
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?artID=<?php echo$values['artID']?>&action=del";
			break;
	}
}

function deleteField(text,ev){
	parent.sConfirmationObj.show('<?php echo $sLang["support"]["detailsart_should_the_field"] ?> '+text+' <?php echo $sLang["support"]["detailsart_really_be_deleted"] ?>',window,'deleteField',ev);
}

function deleteForm(text,ev){
	parent.sConfirmationObj.show('<?php echo $sLang["support"]["detailsart_should_the_form"] ?> '+text+' <?php echo $sLang["support"]["detailsart_really_be_deleted"] ?>',window,'deleteForm',ev);
}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>

<style>
.fixfloat
{
	height: 10px;
	clear: both;
}
.tool-tip {
	color: #fff;
	width: 139px;
	z-index: 13000;
}
a.help {
	width: auto;
	padding:0pt 0pt 0pt 20px;
}
 
.tool-title {
	font-weight: bold;
	font-size: 11px;
	margin: 0;
	color: #9FD4FF;
	padding:0px;
	background: url(bubble.png) top left;
}
 
.tool-text {
	font-size: 11px;
	padding: 4px 8px 8px;
	background: url(bubble.png) bottom right;
}
</style>
</head>
<body>

<form enctype="multipart/form-data" method="POST" id="ourForm" name="ourForm" action="detailsArt.php">
<?php if(!empty($values['artID'])) {?>
<input type="hidden" name="artID" value="<?php echo$values['artID']?>">
<?php }?>
<?php if(!empty($values['catID'])) {?>
<input type="hidden" name="catID" value="<?php echo$values['catID']?>">
<?php }?>
<fieldset><legend><?php echo $sLang["support"]["detailsart_edit_form"] ?></legend>
<ul>
<li style="clear:both;">
	<label for="name"><?php echo $sLang["support"]["detailsart_name"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo$values['name']?>" name="name">
</li>
<li style="clear:both;">
	<label for="name">Link zum Formular (eMail):</label>
	shopware.php?sViewport=support&sFid=<?php echo$values['artID']?>
	
</li><li style="clear:both;">
<label for="name">Link zum Formular (Support/Rma):</label>
	shopware.php?sViewport=rma&sFid=<?php echo$values['artID']?></li>
	
</li><li style="clear:both;">
<label for="name">Link zum Formular <br>(Ticket System):</label>
<?php
	if (!$sCore->sCheckLicense("","",$sCore->sLicenseData["sTICKET"])){
		echo "<font color=red>nicht lizenziert</font>";
	}else{
		echo "shopware.php?sViewport=ticket&sFid=".$values['artID'];
	}
?>	
	</li>



	
<li style="clear:both;">
<label for="email"><?php echo $sLang["support"]["detailsart_email"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo$values['email']?>" name="email">
</li>

<li style="clear:both;">
<label for="email_subject"><?php echo $sLang["support"]["detailsart_email_subject"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo$values['email_subject']?>" name="email_subject">
</li>

<li style="clear:both;">
	<label for="text"><?php echo $sLang["support"]["detailsart_form_head"] ?></label><br>
	<textarea id="text" cols="50" rows="6" name="text" style="height:150px"><?php echo$values['text']?></textarea>
	</div>
</li>

<li style="clear:both;">
	<label for="test"><?php echo $sLang["support"]["detailsart_form_confirmation"] ?></label><br>
	<textarea id="test" cols="50" rows="6" name="text2" style="height:150px"><?php echo$values['text2']?></textarea>
	</div>
</li>

<li style="clear:both;">
	<label for="email_template"><?php echo $sLang["support"]["detailsart_email_template"] ?></label><br>
	<textarea id="email_template" cols="50" rows="6" name="email_template" style="height:150px"><?php echo$values['email_template']?></textarea>
	</div>
</li>

<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="action" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["support"]["detailsart_save"] ?></div></button>
	</li></ul></div>
</li>

</ul>
</fieldset>

<fieldset><legend><?php echo $sLang["support"]["detailsart_edit_field"] ?></legend>

<table cellspacing="0" cellpadding="0" class="listing">
<tbody>
<tr>
	<th><a class="ico help toolTip" title="<?php echo $sLang["support"]["detailsart_with_two_input_fields"] ?>"><?php echo $sLang["support"]["detailsart_name_1"] ?></a></th>
	<th><?php echo $sLang["support"]["detailsart_title"] ?></th>
	<th><?php echo $sLang["support"]["detailsart_typ"] ?></th>
	<th><?php echo $sLang["support"]["detailsart_look"] ?></th>
	<th><?php echo $sLang["support"]["detailsart_position"] ?></th>
	<th><a class="ico help toolTip" title="<?php echo $sLang["support"]["detailsart_values_in_the_selection_fields"] ?>"><?php echo $sLang["support"]["detailsart_options"] ?></a></th>
	<th><a class="ico help toolTip" title="Smarty Code ist erlaubt."><?php echo $sLang["support"]["detailsart_comment"] ?></a></th>
	<th><a class="ico help toolTip" title="Optimal. Smarty Code ist erlaubt."><?php echo $sLang["support"]["detailsart_error_message"] ?></a></th>
	<th><?php echo $sLang["support"]["detailsart_required"] ?></th>
	<th></th>
</tr> 
<?php if(count($values['field'])) { foreach ($values['field'] as $key => $value) {?>
<tr class="rowcolor2">

<th class="" nowrap>
	<input class="w100" style="height:25px" value="<?php echo$value['name']?>" name="field[<?php echo$key?>][name]">
</th>
<th nowrap>	<input class="w100" style="height:25px" value="<?php echo$value['label']?>" name="field[<?php echo$key?>][label]">
</th>
<th nowrap> 
<select class="w100" id="field[<?php echo$key?>][typ]" name="field[<?php echo$key?>][typ]">
	<option selected="selected" value=""><?php echo $sLang["support"]["detailsart_please_select"] ?></option>
	<option <?php if($value['typ']=="text") echo "selected";?> value="text"><?php echo $sLang["support"]["detailsart_field"] ?></option>
	<option <?php if($value['typ']=="text2") echo "selected";?> value="text2"><?php echo $sLang["support"]["detailsart_two_fields"] ?></option>
	<option <?php if($value['typ']=="radio") echo "selected";?> value="radio"><?php echo $sLang["support"]["detailsart_radio_button"] ?></option>
	<option <?php if($value['typ']=="select") echo "selected";?> value="select"><?php echo $sLang["support"]["detailsart_selection_field"] ?></option>
	<option <?php if($value['typ']=="textarea") echo "selected";?> value="textarea"><?php echo $sLang["support"]["detailsart_text_field"] ?></option>
	<option <?php if($value['typ']=="checkbox") echo "selected";?> value="checkbox"><?php echo $sLang["support"]["detailsart_checkbox"] ?></option>
	<option <?php if($value['typ']=="email") echo "selected";?> value="email"><?php echo $sLang["support"]["detailsart_email_1"] ?></option>
</select>
</th>
<th nowrap>
<select class="w100" id="field[<?php echo$key?>][class]" name="field[<?php echo$key?>][class]">
	<option <?php if(empty($value['class'])) echo "selected";?> value=""><?php echo $sLang["support"]["detailsart_please_select"] ?></option>
	<option <?php if($value['class']=="normal") echo "selected";?> value="normal"><?php echo $sLang["support"]["detailsart_normal"] ?></option>
	<option <?php if($value['class']=="plz;ort") echo "selected";?> value="plz;ort"><?php echo $sLang["support"]["detailsart_city_and_zip"] ?></option>
	<option <?php if($value['class']=="strasse;nr") echo "selected";?> value="strasse;nr"><?php echo $sLang["support"]["detailsart_street_and_number"] ?></option>
</select>
</th>
<th class="" nowrap>
	<input class="w100" style="height:25px" value="<?php echo$value['position']?>" name="field[<?php echo$key?>][position]">
</th>
<th class="" nowrap>
	<input class="w100" style="height:25px" value="<?php echo$value['value']?>" name="field[<?php echo$key?>][value]">
</th>

<th class="" nowrap>
	<input class="w100" style="height:25px" value="<?php echo$value['note']?>" name="field[<?php echo$key?>][note]">
</th>
<th class="" nowrap>
	<input class="w100" style="height:25px" value="<?php echo$value['error_msg']?>" name="field[<?php echo$key?>][error_msg]">
</th>
<th class="" nowrap>
	<?php if($value['required']==1) {?>
	<input type="radio" name="field[<?php echo$key?>][required]" value="0" style="float:none;" ><?php echo $sLang["support"]["detailsart_no"] ?>
	<input type="radio" name="field[<?php echo$key?>][required]" value="1" style="float:none;" checked><?php echo $sLang["support"]["detailsart_yes"] ?><br />
	<?php } else {?>
	<input type="radio" name="field[<?php echo$key?>][required]" value="0" style="float:none;" checked><?php echo $sLang["support"]["detailsart_no"] ?>
	<input type="radio" name="field[<?php echo$key?>][required]" value="1" style="float:none;" ><?php echo $sLang["support"]["detailsart_yes"] ?><br />
	<?php }?>
</th>
<th class="last-child" nowrap>
	<a class="ico delete" style="cursor:pointer" onclick="deleteField('<?php echo$value['label']?>',<?php echo $key ?>)"></a>	
</th>
</tr>
<?php }}?>
<tr>
<th class="" nowrap>
	<input class="w100" style="height:25px" value="" name="field_name_new">
</th>
<th class="last-child" nowrap>
	<input class="w100" style="height:25px" value="" name="field_label_new">
</th>
</tr>
</tbody>
</table>
<ul>
<li style="clear:both;">
<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="action" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["support"]["detailsart_edit"] ?></div></button>
	</li></ul></div>
</li>
</ul>
</fieldset>

</form>

<?php if(isset($values['artID'])) {?>


<fieldset><legend><?php echo $sLang["support"]["detailsart_delete_this_form"] ?></legend>
<ul>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="" name="action" onclick="deleteForm('<?php echo $values['name'] ?>',<?php echo $values["artID"] ?>)" value="del" class="button"><div class="buttonLabel"><?php echo $sLang["support"]["detailsart_delete"] ?></div></button>
	</li></ul></div>
</li>
</ul>
</fieldset>

<?php }?>

<form enctype="multipart/form-data" method="POST" id="ourForm2" name="ourForm2" action="detailsCat.php">
<fieldset><legend><?php echo $sLang["support"]["detailsart_form_overview"] ?></legend>
<ul>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="" name="action" type="submit" value="del" class="button"><div class="buttonLabel"><?php echo $sLang["support"]["detailsart_show"] ?></div></button>
	</li></ul></div>
</li>
</ul>
</fieldset>
</form>

</body>
</html>