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

if ($_POST["sAction"]=="saveArticle" && !empty($_GET["edit"]))
{	
	$fields = array(
		'description','template','class','table','additionaldescription','position','active',
		'debit_percent','surcharge','surchargestring','embediframe','hideprospect', 'esdactive'
	);
	$upset = array();
	foreach ($fields as $field)
	{
		if(isset($_POST[$field]))
		{
			$upset[] = "`$field`='".mysql_real_escape_string($_POST[$field])."'";
		}
	}
	$sql = '
		UPDATE s_core_paymentmeans
		SET '.implode(', ', $upset).'
		WHERE id='.intval($_GET['edit']).'
	';
	$insertArticle = mysql_query($sql);
		
	if ($insertArticle) {
		$sInform = $sLang["payment"]["payment_entry_saved"];
	} else {
		echo mysql_error();
	}
	
	if(!empty($_REQUEST['countries']))
	{
		foreach ($_REQUEST['countries'] as &$value)
		{
			$value = (int) $value;
		}
		$countries = implode(',',$_REQUEST['countries']);
	}
	$sql = 'DELETE FROM s_core_paymentmeans_countries WHERE paymentID='.intval($_GET['edit']);
	if(!empty($countries))
	{
		$sql .= ' AND countryID NOT IN ('.$countries.')';
	}
	mysql_query($sql);
	if(!empty($countries))
	{
		$sql = '
			INSERT IGNORE INTO s_core_paymentmeans_countries (paymentID, countryID)
			SELECT '.intval($_GET['edit']).', c.id
			FROM s_core_countries c
			WHERE c.id IN ('.$countries.')
		';
		mysql_query($sql);
	}
	
	if(!empty($_REQUEST['subshops']))
	{
		foreach ($_REQUEST['subshops'] as &$value)
		{
			$value = (int) $value;
		}
		$subshops = implode(',',$_REQUEST['subshops']);
	}
	$sql = 'DELETE FROM s_core_paymentmeans_subshops WHERE paymentID='.intval($_GET['edit']);
	if(!empty($subshops))
	{
		$sql .= ' AND subshopID NOT IN ('.$subshops.')';
	}
	mysql_query($sql);
	if(!empty($subshops))
	{
		$sql = '
			INSERT IGNORE INTO s_core_paymentmeans_subshops (paymentID, subshopID)
			SELECT '.intval($_GET['edit']).', s.id
			FROM s_core_multilanguage s
			WHERE s.id IN ('.$subshops.')
		';
		mysql_query($sql);
	}
}

if ($_GET["edit"]){
	$getSite = mysql_query("
	SELECT * FROM s_core_paymentmeans WHERE id={$_GET["edit"]}
	");
	
	if (!@mysql_num_rows($getSite)){
		$sInform = $sLang["payment"]["payment_mail_not_found"];
	}else {		
		$getSite = mysql_fetch_array($getSite);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>

<head>
<title>..</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="../../../vendor/tinymce/tiny_mce.js"></script>

</head>

<body >
<?php
$sCore->sInitTranslations(1,"config_payment","true");
?>


<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
			break;
	}
}

function deleteArticle(ev,text){
		parent.sConfirmationObj.show('<?php echo $sLang["payment"]["payment_should_the_site"] ?> "'+text+'" <?php echo $sLang["payment"]["payment_really_be_deleted"] ?>',window,'deleteArticle',ev);
	}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
};
</script>
<fieldset class="col2_cat2">
<legend><a class="ico help"></a><?php echo $sLang["payment"]["payment_Overview_Payment_methods"] ?></legend>
<?php echo $sLang["payment"]["payment_You_can_check_availability"] ?>
</fieldset>
<?php
if ($_GET["edit"]){
?>
<form enctype="multipart/form-data" method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
		<input type="hidden" name="sAction" value="saveArticle">
		<fieldset>
		<legend><?php echo $sLang["payment"]["payment_Payment_methods"] ?></legend>
		<ul>
		
		<!-- Felder ausgeben -->
		<?php
		$getFields = mysql_query("SHOW COLUMNS FROM s_core_paymentmeans");
		
		
		$substitute = $sLang["payment"]["payment_array"];
		
		
		while ($row = mysql_fetch_assoc($getFields)) {
		
		
		   	if ($substitute[$row["Field"]]!="hide"){

		   	$fieldName = $row["Field"];
		   	   if ($substitute[$row["Field"]]){

		   	   	$column = $substitute[$row["Field"]];

		   	   }else {

		   	   	$column = ucfirst($row["Field"]);

		   	   }
				
		   	   if ($fieldName=="additionaldescription"){
		   	   //	$getSite[$row["Field"]] = 
			   	   	  echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><textarea name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"width:350px\" cols=\"50\" rows=\"5\" class=\"w200\">{$getSite[$row["Field"]]}</textarea>";
			   	   	  if ($_GET["edit"]){
			   	   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_payment","{$getSite["id"]}");
				   	  }
				   	  echo "</li><li class=\"clear\"/>";
		   	   }elseif ($fieldName=="description"){
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"$fieldName\" style=\"width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" />";
		   	    	if ($_GET["edit"]){
			   	   	   		echo $sCore->sBuildTranslation("$fieldName","$fieldName","1","config_payment","{$getSite["id"]}");
				   	}
		   	   		echo "</li><li class=\"clear\"/>";
		   	   }elseif ($fieldName=="surchargestring"){
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" /><br/><p style=\"font-weight:bold;margin-left:160px\">
		   	   		".$sLang["payment"]["payment_Please_use_the_following_format"]."<br />".$sLang["payment"]["payment_The_ISO_country_codes"]."
		   	   		</p></li>";
		   	    	echo "<li class=\"clear\"/>";
		   	    	?>
		   	    	<?php
		   	   }
		   	   else {
		   	   		echo "<li><label style=\"width:150px; text-align:left\" for=\"name\">{$column}:</label><input name=\"{$fieldName}\" type=\"text\" id=\"\" style=\"width:250px\" class=\"w200\" value=\"{$getSite[$row["Field"]]}\" /></li>";
		   	    	echo "<li class=\"clear\"/>";
		   	   }
		   	}
	   }
		?>
		<li>
			<label style="width:150px; text-align:left" for="countries">Länder:</label>
			<select id="countries" name="countries[]" style="width:257px;height:100px;font-size:10px;" class="w200" multiple="multiple">
			<?php
			$sql = '
				SELECT id, countryname as name, IF(pc.paymentID,1,0) as selected
				FROM `s_core_countries` c
				LEFT JOIN s_core_paymentmeans_countries pc
				ON pc.countryID=c.id
				AND pc.paymentID='.intval($_GET['edit']).'
				ORDER BY position, countryname
			';
			$result = mysql_query($sql);
			if($result && mysql_num_rows($result))
			while ($row = mysql_fetch_assoc($result)) {
			?>
				<option value="<?php echo $row['id'];?>"<?php if($row['selected']) {?> selected="selected"<?php }?>><?php echo htmlentities($row['name']);?></option>
			<?php
			}
			?>
			</select>
		</li>
		<li>
			<label style="width:150px; text-align:left" for="subshops">Shops:</label>
			<select id="subshops" name="subshops[]" style="width:257px;height:100px;font-size:10px;" class="w200" multiple="multiple">
			<?php
			$sql = '
				SELECT id, name, IF(ps.subshopID,1,0) as selected
				FROM `s_core_multilanguage` s
				LEFT JOIN s_core_paymentmeans_subshops ps
				ON ps.subshopID=s.id
				AND ps.paymentID='.intval($_GET['edit']).'
				ORDER BY `default` DESC, name
			';
			$result = mysql_query($sql);
			if($result && mysql_num_rows($result))
			while ($row = mysql_fetch_assoc($result)) {
			?>
				<option value="<?php echo $row['id'];?>"<?php if($row['selected']) {?> selected="selected"<?php }?>><?php echo htmlentities($row['name']);?></option>
			<?php
			}
			?>
			</select>
		</li>
		<li class="clear"/>
		</ul>
		<div class="buttons" id="buttons">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate">
				<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["payment"]["payment_save"] ?></div></button>
				</li>	
			</ul>
		</div>
		</fieldset>
		</form>
        
<?php
}
?>
<?php
include("../../../backend/elements/window/translations.htm");
?>
<script type="text/javascript" src="../../../backend/js/translations.php"></script>
</body>
</html>