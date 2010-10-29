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


// =======================================================================================
if(isset($_POST['sAction'])){
	// ===================================================================================
	// check user auth
	// ===================================================================================
	$sResetUser = mysql_real_escape_string($_POST['sResetUser']);
	$sResetPass = mysql_real_escape_string($_POST['sResetPass']);
	$sResetPassMd5 = md5("A9ASD:_AD!_=%a8nx0asssblPlasS$".md5($sResetPass));
	
	$chkAuth = mysql_query("
		SELECT `admin` FROM `s_core_auth`
		WHERE `username` = '{$sResetUser}'
		AND password = '{$sResetPassMd5}'"
	);
	
	if(mysql_num_rows($chkAuth) != 0){
		$admin = mysql_result($chkAuth, 0, 'admin');
		if(!empty($admin)){
			
			// ===========================================================================
			// Delete articles && categories via API
			// ===========================================================================
			$import = Shopware()->Api()->Import();
			
			$import->sDeleteAllArticles();
			$import->sDeleteAllCategories();
			
			//=============================================================================
			// Delete Supplier
			//=============================================================================
			
			mysql_query("TRUNCATE TABLE `s_articles_supplier`");
			
			$sSuccess = true;
				
		}else{
			$sErrorText = "Benutzer verfügt nicht über die nötigen Administratorrechte!";
			$sAuth = false;
		}
	}else{
		$sErrorText = "Benutzername oder Passwort falsch!";
		$sAuth = false;
	}
}
// =======================================================================================
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

<style type="text/css">
</style>

<body>

<?php 
 
 if($sSuccess == true)
 {
 	?>
 		<fieldset class='col2_cat2' style='margin-top:-34px;'>
		<legend><a class="ico accept"/></a>Aktion erfolgreich!</legend>
			<p>
				Der Shop wurde erfolgreich zurückgesetzt.
			</p>
		</fieldset>
 	<?php
 	exit;
 }
 
 
 //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

 echo "<form method='POST'>";
 
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

?>

<fieldset class='col2_cat2' style='margin-top:-34px;'>
<legend><a class="ico exclamation"/></a>ACHTUNG</legend>
	<p>
		Durch diese Funktion werden alle Artikel und Kategorien entfernt.<br>
		Bitte bestätigen Sie die Ausführung durch ein Administrator-Benutzerkonto.
	</p>
</fieldset>

<div class="clear"></div>

<fieldset class='col2_cat2' style='margin-top:-43px;'>
<legend>Administrator-Benutzer</legend>

	<?php
	if(!empty($sErrorText))
	{
		echo "<div style='margin-left:108px;font-weight:bold;color:red;'>";
		echo $sErrorText;
		echo "</div>";
		echo "<div class='clear' style='height:20px;'></div>";
	}
	?>

	<label>Benutzer</label>
	<input id="sResetUser" name="sResetUser" type="text" class="w200" value="<?php echo htmlentities($_POST['sResetUser']); ?>" autocomplete="off"/>
	<div class="clear" style="height:5px;"></div>
	
	
	<label>Passwort</label>
	<input id="sResetPass" name="sResetPass" type="password" class="w200" value="" autocomplete="off"/>
	<div class="fixfloat"/>
	
</fieldset>

<div class="fixfloat"/>

<?php
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

echo "<div id='buttons' class='buttons' style='left:5px; position:relative; top:-24px;'><ul><li class='buttonTemplate' style='display: block;'>";
	echo "<button class='button' value='save' type='submit' name='sAction' id='save' onclick='return validateForm();'><div class='buttonLabel'>Shop zurücksetzen</div></button>";
echo "</div>";

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

echo "</form>";
?>


<script type="text/javascript">
function validateForm()
{	
	var username = $('sResetUser').value;
	var password = $('sResetPass').value;
	
	if(username.trim() == "" || password.trim() == "")
	{
		$('sResetUser').setStyle('border', '1px solid red');
		$('sResetPass').setStyle('border', '1px solid red');
		parent.parent.parent.Growl("Bitte geben Sie einen Benutzer und ein Passwort ein!");
		return false;
	}
	
	//no error || submit form
	return true;
}
</script>

</body>

</html>