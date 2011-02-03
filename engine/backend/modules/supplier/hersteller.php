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
$_GET["edit"] = intval($_GET["edit"]);

// *****************
// SPEICHERN DER HERSTELLER
// ===========================================
if(isset($_POST['sDeleteImg']))
{
	$abfrage = mysql_query("
	SELECT img FROM s_articles_supplier WHERE id=".$_GET["edit"]."
	");
	$img = mysql_fetch_array($abfrage);
	
	$uploaddir = '../../../../images/supplier/';
	unlink($uploaddir.$img['img']);
	mysql_query("
		UPDATE `s_articles_supplier` SET `img` = '' WHERE `id` = '{$_GET["edit"]}' LIMIT 1 ;
	");
}
elseif ($_POST["saveNow"]){
	// Alle Bedingungen erfüllt??
	#echo "TEST"; 
	if (!$_POST["txtName"]){
		$fehler = 1;
	}
	
	$_POST["txtName"] = preg_replace("/\r|\n/s", "", $_POST["txtName"]);
	
	//$_POST["txtName"] = htmlspecialchars($_POST["txtName"]);
	
	if (!$fehler){
		// Falls Datei Upload
		
			if ($_FILES['bild']['tmp_name']){
			
			    $uploaddir = '../../../../images/supplier/';
			    $datei = explode(".",$_FILES['bild']['name']);
			    $endung = $datei[1];
			    
			    $file = $random = md5(uniqid(rand())).".".$endung;
			    if (!move_uploaded_file($_FILES['bild']['tmp_name'], $uploaddir.$file)){
			    	
			    	$sError = $sLang["supplier"]["hersteller_upload_error"];
			    }else {
			    	chmod($uploaddir.$file,0755);
			    	$source = $file;
			    }
			}
			#echo $source; 
		if ($_GET["edit"]){
			$_POST["txtName"] = mysql_real_escape_string($_POST["txtName"]);
			// Editieren
			if ($source){
				$insertHersteller = mysql_query("
				UPDATE s_articles_supplier SET name='{$_POST["txtName"]}', img='$source', link='{$_POST["supplierLink"]}'
				WHERE id={$_GET["edit"]}
				");
			}else {
				$insertHersteller = mysql_query("
				UPDATE s_articles_supplier SET name='{$_POST["txtName"]}', link='{$_POST["supplierLink"]}'
				WHERE id={$_GET["edit"]}
				");
			}
		}else {
			// Hinzufügen
			$_POST["txtName"] = mysql_real_escape_string($_POST["txtName"]);
			$sql = "
			INSERT INTO s_articles_supplier (name, img, link)
			VALUES ('{$_POST["txtName"]}','$source','{$_POST["supplierLink"]}')
			";
			
			$insertHersteller = mysql_query($sql);
			
			$_GET["edit"] = mysql_insert_id();
		}	
		if ($insertHersteller){
			$sInform = "Hersteller wurde erfolgreich gespeichert";
		}else {
			$sError = "Hersteller konnte NICHT angelegt werden";
		}
	}else {
			$sError = "Bitte geben Sie den Namen des Herstellers ein";
	}
	if (!$_GET["edit"] && empty($_GET["new"])){
		$_GET["new"] = true;
	}
}
// ===========================================
// Delete?
if ($_GET["delete"]){
	$abfrage = mysql_query("
	SELECT img FROM s_articles_supplier WHERE id=".$_GET["delete"]."
	");
	$deletegfx = mysql_result($abfrage,0,"img");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_supplier WHERE id=".$_GET["delete"]."
	");
	
	if ($abfrage){
		$sInform = $sLang["supplier"]["hersteller_supplier_deleted"];
		if(!empty($deletegfx)) @unlink ("../gfx_hersteller/".$deletegfx);
	}else {
		$sError = $sLang["supplier"]["hersteller_supplier_not_deleted"]."<br>".mysql_error();
	}
}


// Edit?
if ($_GET["edit"]){
	$abfrage = mysql_query("
	SELECT * FROM s_articles_supplier WHERE id=".$_GET["edit"]."
	");
	
	$editArray = mysql_fetch_array($abfrage);
	
	$txtName = $editArray["name"];
	$_POST["supplierLink"] = $editArray["link"];
	$file = $editArray["img"];

}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="<?php echo $sLang["supplier"]["hersteller_hamann-media"] ?>"/>
<meta name="copyright" content="<?php echo $sLang["supplier"]["hersteller_2007_hamann"] ?>" />
<meta name="company" content="<?php echo $sLang["supplier"]["hersteller_eBusiness"] ?>" />
<meta name="reply-to" content="<?php echo $sLang["supplier"]["hersteller_eMail"] ?>" />
<meta name="rating" content="general" />
<meta http-equiv="content-language" content="de" />

<title>Supplier</title>

</head>
<body>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteSupplier":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?delete="+sId;
			break;
		case "deleteSupplierImage":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?deleteImage="+sId;
			break;
		case "newSupplier":
		window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?new=1";
			break;
		case "saveSupplier":
		try {
			$('save').submit();
		}catch (e) {} 
		
		break;
	}
}

function deleteSupplier(ev,text,number){
		if (number){
			parent.Growl('<?php echo $sLang["supplier"]["hersteller_this_supplier"] ?> "'+number+'" <?php echo $sLang["supplier"]["hersteller_articles_assigned"]?>');
		}else {
			parent.parent.sConfirmationObj.show('<?php echo $sLang["supplier"]["hersteller_the_supplier"] ?> "'+text+'" <?php echo $sLang["supplier"]["hersteller_really_delete"] ?>',window,'deleteSupplier',ev);
		}
	}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.sWindows.focus.shake(50);";
		}
	?>
	
};
</script>

<?php
if ($_GET["edit"] || $_GET["new"]){
?>
<fieldset>
	<legend><?php if (!$_GET["edit"]) { echo $sLang["supplier"]["hersteller_new_supplier"];  } else {  echo $sLang["supplier"]["hersteller_edit_supplier"];  } ?></legend>
	<form name="save" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER["PHP_SELF"]."?edit={$_GET['edit']}"; ?>">
	<input type="hidden" name="saveEdit" value="<?php echo $_GET['edit']; ?>">
  	<input type="hidden" name="saveNow" value="1">
	 <?php
	 if ($file){
	 	echo "<div style=\"padding:15px;\"><img src=\"../../../../images/supplier/$file\"></div>";
	 }
	 ?>
	
	 	 
	<ul>
	<li><label for="name"><?php echo $sLang["supplier"]["hersteller_supplier_name"] ?></label>
	<input name="txtName" type="text" id="txtName" class="w200" value="<?php echo $txtName; ?>" />
	</li>
	<li class="clear"/>
	</ul>
	 
	<ul>
	<li>
	<label for="img"><?php echo $sLang["supplier"]["hersteller_supplier_picture"] ?></label>
	<input type="file" name="bild" id="Filedata" /></li>
	<li class="clear"></li>
	</ul>
	
	<ul>
	<li>
	<label for="img"><?php echo $sLang["supplier"]["hersteller_supplier_homepage"] ?></label>
	<input type="text" class="w200" name="supplierLink" value="<?php echo $_POST["supplierLink"] ?>" id="Filedata" /></li>
	<li class="clear"></li>
	<li>
		
	<br /><br />
	
	<div class="buttons" id="div" style="float: left; width:110px">
      <ul>
      	<li id="buttonTemplate" class="buttonTemplate">
        <button type="submit" value="send" class="button">
        <div class="buttonLabel">Speichern</div>
        </button>
       </li>
      </ul>
    </div>
    
    <?php 
	if(!empty($file))
	{
	?>
		<div class="buttons" id="div" style="float: left;">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate">
        <button name="sDeleteImg" type="submit" value="send" class="button">
        <div class="buttonLabel">Herstellerbild entfernen</div>
        </button>
       	</li>
		</ul>
		</div>
		
		<ul>
		<li class="clear"/>
		</ul>
		
	<?php
	}
	?>
    
     </li>
	</ul>
</form>
</fieldset>
<?php
}
?>

</body>
</html>