<?php
define('sAuthFile', 'sGUI');
define('sConfigPath',"../../../../");
include("../../../backend/php/check.php");
$result = new checkLogin();
$result = $result->checkUser();
if ($result!="SUCCESS"){
	echo $sLang["presettings"]["orderstatemail_fail"];
	die();
}


if(!empty($_REQUEST['artID']))
	$values['artID'] = intval($_REQUEST['artID']);
if(!empty($_REQUEST['action'])) if($_REQUEST['action']=="save")
{
	
	$values['content'] = mysql_real_escape_string($_REQUEST['content']);
	$values['frommail'] = mysql_real_escape_string($_REQUEST['frommail']);
	$values['fromname'] = mysql_real_escape_string($_REQUEST['fromname']);
	$values['subject'] = mysql_real_escape_string($_REQUEST['subject']);
	$values['mail'] = intval($_REQUEST['mail']);
	$sql = "
		SELECT `id`
		FROM `s_core_config_mails`
		WHERE `name`='sORDERSTATEMAIL{$values['artID']}'";
	$result = mysql_query($sql);
	if($result) if(mysql_num_rows($result))
	{
		$sql2 = "
			UPDATE `s_core_config_mails` 
			SET 
				`content` = '{$values['content']}',
				`frommail` = '{$values['frommail']}',
				`fromname` = '{$values['fromname']}',
				`subject` = '{$values['subject']}'
			WHERE `name` = 'sORDERSTATEMAIL{$values['artID']}'";
	}
	if (empty($sql2)) 
	{
		$sql2 = "
			INSERT INTO `s_core_config_mails` ( 
				`name` , `frommail` , `fromname` , `subject` , `content`
			) VALUES (
				'sORDERSTATEMAIL{$values['artID']}', '{$values['frommail']}', '{$values['fromname']}', '{$values['subject']}', '{$values['content']}'
			);";
	}
	$result = mysql_query($sql2);
	$sql = "
		UPDATE `s_core_states` 
		SET 
			`mail` = '{$values['mail']}'
		WHERE `id` = '{$values['artID']}'";
	$result = mysql_query($sql);
}
if(!empty($values['artID']))
{
	$sql = "
		SELECT `name` , `frommail` , `fromname` , `subject` , `content` , `ishtml`
		FROM `s_core_config_mails`
		WHERE `name`='sORDERSTATEMAIL{$values['artID']}'";
	$result = mysql_query($sql);
	if($result) if(mysql_num_rows($result))
	{
		$values = mysql_fetch_assoc($result);
		$values['artID'] = intval($_REQUEST['artID']);
	}
}
	$sql = "
		SELECT `id`, `description`, `group`, `mail`
		FROM `s_core_states`
		WHERE `id` >= 0
		ORDER BY `position`
	";
	$result = mysql_query($sql);
	if($result)
	{
		while ($row = mysql_fetch_assoc($result))
		{
			$rows[$row['group']][$row['id']] = $row;
			if($row['id']==$values['artID'])
				$values['mail'] = $row['mail'];
		}
	}
	$sCore->sInitTranslations(1,"config_mails","true");
?>
<html>
<head>
<title><?php echo $sLang["presettings"]["orderstatemail_Service_Administration"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />

</head>
<body style="padding-top:0; margin:0;">
<?php if(!empty($values['artID'])) {?>
<form enctype="multipart/form-data" method="post" id="form" name="form">
<fieldset style="margin:0pt;"><legend><?php echo $sLang["presettings"]["orderstatemail_edit"] ?></legend>
<ul>
<li style="clear:both;">
<label for="subject"><?php echo $sLang["presettings"]["orderstatemail_Subject"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo $values['subject']?>" name="subject" id="subject">
	<?php 
	echo $sCore->sBuildTranslation("subject","subject","1","config_mails",'sORDERSTATEMAIL'.$values['artID']);
	?>
</li>
<li style="clear:both;">
<label for="fromname"><?php echo $sLang["presettings"]["orderstatemail_sender"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo$values['fromname']?>" name="fromname" id="fromname">
	<?php 
	echo $sCore->sBuildTranslation("fromname","fromname","1","config_mails",'sORDERSTATEMAIL'.$values['artID']);
	?>
</li>
<li style="clear:both;">
<label for="frommail"><?php echo $sLang["presettings"]["orderstatemail_Address"] ?></label>
	<input class="w200" style="height:25px" value="<?php echo$values['frommail']?>" name="frommail" id="frommail">
	<?php 
	echo $sCore->sBuildTranslation("frommail","frommail","1","config_mails",'sORDERSTATEMAIL'.$values['artID']);
	?>
</li>
<li style="clear:both;">
<label for="content"><?php echo $sLang["presettings"]["orderstatemail_email-text"] ?></label>
	<textarea style="height:250px;width:400px;" name="content" id="content"><?php echo htmlentities($values['content']);?></textarea>
	<?php 
	echo $sCore->sBuildTranslation("content","content","1","config_mails",'sORDERSTATEMAIL'.$values['artID']);
	?>
</li>
<li style="clear:both;display:none">
<label for="mail"><?php echo $sLang["presettings"]["orderstatemail_activ"] ?></label>
	<input name="mail" value="1" style="float: none;" type="radio"<?php if (!empty($values['mail'])) echo " checked=\"checked\""; ?>> ja <input name="mail" value="0" style="float: none;" type="radio"<?php if (empty($values['mail'])) echo " checked=\"checked\""; ?>><?php echo $sLang["presettings"]["orderstatemail_no"] ?> </li>
<li style="clear:both;">
	<div class="buttons" id="buttons"><ul><li style="display: block;" class="buttonTemplate">
	<button id="save" name="action" type="submit" value="save" class="button"><div class="buttonLabel"><?php echo $sLang["presettings"]["orderstatemail_save"] ?></div></button>
	</li></ul></div>
</li>
</ul>
</fieldset>
</form>
<?php }?>



<?php foreach ($rows as $name=>$row) { ?>
<?php
if ($name=="payment"){
	$groupname = $sLang["presettings"]["orderstatemail_paymentstatus"];
}else {
	$groupname = $sLang["presettings"]["orderstatemail_orderstatus"];
}
?>
<fieldset id="notfound" style="margin-top:0"><legend><?php echo $sLang["presettings"]["orderstatemail_group"] ?> <?php echo $groupname ?></legend>

<table cellspacing="0" cellpadding="0" class="listing">
<tbody>
<?php foreach ($row as $id=>$rw) { ?>
<tr>
	<td><img style="margin: 0pt 15px 0pt 0pt;" src="../../../backend/img/default/icons4/mail.png"/> <?php echo $rw['description']?></th>
	<td><a class="ico pencil" style="cursor: pointer;" href="?artID=<?php echo $id?>"/></th>
</tr>
<?php }?>
</tbody>
</table>
</fieldset>
<?php
include("../../../backend/elements/window/translations.htm");
?>
<?php }?>
<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>



