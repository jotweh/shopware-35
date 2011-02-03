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
if($_REQUEST['action']=="del"&&!empty($values['artID']))
{
	$sql = "DELETE FROM `s_cms_support` WHERE `id` = '{$values['artID']}' LIMIT 1;";
	$result = mysql_query($sql);
	$sql = "DELETE FROM `s_cms_support_fields` WHERE `supportID` = '{$values['artID']}'";
	$result = mysql_query($sql);	
}}



	$sql = "
		SELECT 
			 `id`, `name` , `text` , `email` , `email_template` , `email_subject` 
		FROM 
			`s_cms_support`";

	
	$result = mysql_query($sql);
	while ($cat = mysql_fetch_assoc($result))
	{
		$cats[$cat['id']] = $cat;
	}
?>


<html>
<head>
<title><?php echo $sLang["support"]["detailscat_help_administration"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
</head>
<script>
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteField":
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?artID=<?php echo$values['artID']?>&action=delField&fieldID="+sId;
			break;
		case "deleteForm":
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?artID="+sId+"&action=del";
			break;
		case "duplicate":
			//window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?artID="+sId+"&action=del";
			var myAjax = new Ajax("<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/duplicate.php",{method: 'post', onComplete: function(res){
			
			parent.parent.Growl('Form wurde dupliziert');
			location.href = location.href;
			/*
			// Sample call
			$_REQUEST["duplicate"] = 5;	// Contact Form
			$_REQUEST["tableM"] = "s_cms_support";
			$_REQUEST["tableMName"] = "name";
			$_REQUEST["tableMNameAttach"] = " Kopie";
			$_REQUEST["tableS"] = "s_cms_support_fields|supportID#";
			*/
			}}).request({'duplicate':sId,'tableM':'s_cms_support','tableMName':'name','tableMNameAttach':' Kopie','tableS':'s_cms_support_fields|supportID#'			
			});
			break;
	}
}

function deleteField(text,ev){
	parent.sConfirmationObj.show('<?php echo $sLang["support"]["detailscat_should_the_field"] ?> '+text+' <?php echo $sLang["support"]["detailscat_really_be_deleted"] ?>',window,'deleteField',ev);
}

function deleteForm(text,ev){
	parent.sConfirmationObj.show('<?php echo $sLang["support"]["detailscat_should_the_form"] ?> '+text+' <?php echo $sLang["support"]["detailscat_really_be_deleted"] ?>',window,'deleteForm',ev);
}

function duplicate(text,ev){
	parent.sConfirmationObj.show('<?php echo $sLang["support"]["detailscat_should_the_form"] ?> '+text+' wirklich dupliziert werden?',window,'duplicate',ev);
}
</script>
<body>

<fieldset><legend><?php echo $sLang["support"]["detailscat_form_overview"] ?></legend>
<table cellspacing="0" cellpadding="0" class="listing">
<thead>
<tr>
	<th style="font-weight:bold;font-size:11px; color:#999;"><?php echo $sLang["support"]["detailscat_name"] ?></th>
	<th style="font-weight:bold;font-size:11px; color:#999;"><?php echo $sLang["support"]["detailscat_email"] ?></th>
	<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<?php foreach ($cats as $key=>$cat) {?>
<tr>
	<th class="th_bold"><?php echo$cat['name']?> (<?php echo$cat['id']?>)</th>
	<th class="th_bold"><?php echo$cat['email']?></th>
	<th class="last-child">
		 <a class="ico pencil" href="detailsArt.php?artID=<?php echo$key?>"></a>
		 <a class="ico delete" style="cursor:pointer" onclick="deleteForm('<?php echo$cat['name']?>',<?php echo $key ?>)"></a>
		 <a class="ico folder_plus" style="cursor:pointer" onclick="duplicate('<?php echo$cat['name']?>',<?php echo $key ?>)"></a>		
	</th>
</tr>
<?php }?>
</tbody>
</table>


</fieldset>

</form>

<?php
if (!$licenceFailed){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm2" name="ourForm2" action="detailsArt.php">
<fieldset><legend><?php echo $sLang["support"]["detailscat_create_form"] ?></legend>
<ul>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["support"]["detailscat_create"] ?></div></button>
	</li></ul></div>
</li>
</ul>
</fieldset>
</form>
<?php
}
?>

</body>
</html>