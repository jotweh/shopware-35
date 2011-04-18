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
	parent.parent.location.reload();
	</script>
	<body>Bitte loggen Sie sich neu ein!</body></html>
	";
	die();
}
// *****************
?>
<?php


if (!$_GET["article"]) die($sLang["articles"]["varianten_no_article"]);


// Delete?
if ($_GET["delete"]){
	$abfrage = mysql_query("
	DELETE FROM s_articles_details WHERE id=".$_GET["delete"]."
	");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_prices WHERE articleDetailsID=".$_GET["delete"]."
	");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_attributes WHERE articleDetailsID=".$_GET["delete"]."
	");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_esd WHERE articleDetailsID=".$_GET["delete"]."
	");
	
	if ($abfrage){
		$sInform = $sLang["articles"]["varianten_variant_deleted"];
	}else {
		$sError = $sLang["articles"]["varianten_variant_delete_failed"]."<br>".mysql_error();
	}
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="Hamann-Media GmbH" />
<meta name="copyright" content="2007, Hamann-Media GmbH" />
<meta name="company" content="Hamann-Media GmbH - eBusiness-Spezialist aus dem Muensterland" />
<meta name="reply-to" content="info@hamann-media.de" />
<meta name="rating" content="general" />
<meta http-equiv="content-language" content="de" />

<title><?php echo $sLang["articles"]["varianten_links"] ?></title>

</head>

<style>


#sorts { 
	position: inherit;
	padding: 0px;
}
 
ul#sorts {
	margin: 0;
}
 
li.sortme {
	/*padding: 4px 8px; */
	padding: 10px 5px;
	margin:0;
	cursor: move;
	list-style: none;
	float: none;
	position:relative;
	background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x;
	height: 18px;
	background-position:0px 1px;
	/*border-bottom: 1px solid #dfdfdf;*/

}
</style>
<script type="text/javascript" src="../../../backend/js/moo12-core.js"></script>
<script type="text/javascript" src="../../../backend/js/moo12-more.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteLink":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?article=<?php echo $_GET["article"]?>&delete="+sId;
			break;
		case "newLink":
		window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?new=1";
			break;
		case "saveLink":
		try {
			$('save').submit();
		}catch (e) {} 
		
		break;
	}
}

function deleteLink(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["varianten_should_the_Variant"]?> "'+text+'" <?php echo $sLang["articles"]["varianten_really_deleted"] ?>',window,'deleteLink',ev);
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
<script>
window.addEvent('domready', function(){
new Sortables($('sorts'), {
	handles: $$('#sorts .handle')
	});
});
</script>
<body>


<?php
if ($_POST["posvariante"]){
	foreach ($_POST["posvariante"] as $position => $id){
		$updatePosition = mysql_query("
		UPDATE s_articles_details SET position = $position WHERE id = $id
		");
	}
}

$query = mysql_query("
SELECT purchaseunit, referenceunit FROM s_articles
WHERE id={$_GET["article"]} AND (purchaseunit != 0 OR referenceunit != 0) 
");
if (@mysql_num_rows($query)){
	$variantError = false;
	?>
	<div class="showthis" style="background-color:#F00;color:#FFF;font-size:12px">
	<a class="ico question_shield"></a>Sie haben für diesen Artikel Grundpreis-Eigenschaften definiert.
	Diese können nicht in Kombination mit Varianten verwendet werden!
	</div>
	<?php
}


// Check ob Konfigurator benutzt wird ...
$query = mysql_query("
SELECT groupID FROM s_articles_groups
WHERE articleID={$_GET["article"]}
");

if (@mysql_num_rows($query)){
	$variantError = true;
	?>
	<div class="showthis">
	<a class="ico question_shield"></a><?php echo $sLang["articles"]["varianten_Regard_delete_multidimensional_variants"] ?>
	</div>
	<?php
}
// Check ob Zusatztext angebeben wurde

$checkAdditionalText = mysql_query("
SELECT additionaltext FROM s_articles_details WHERE articleID={$_GET["article"]} AND kind=1
");

if (!strlen(mysql_result($checkAdditionalText,0,"additionaltext"))){
?>
<?php if (!$variantError) { ?><?php } ?>
<?php
	$variantError = true;
?>
	<div class="showthis">
	<a class="ico question_shield"></a><?php echo $sLang["articles"]["varianten_Regard_enter_Data_first"] ?>
		</div>
<?php
}

if (!$variantError){
?>
<fieldset class="col2_artikeln3" style="margin: 0 0 20px 0;">
<legend style="font-weight:bold;"><a class="ico help"></a> <?php echo $sLang["articles"]["varianten_add_new_variant"] ?></legend>
<?php echo $sLang["articles"]["varianten_Variants_are_intended_to"] ?>
<br /><strong><?php echo $sLang["articles"]["varianten_drag_drop"] ?></strong>
<form method="post" action="<?php echo "artikeln1.inc.php"."?article=".$_GET["article"]."&variante=-1"?>">
<div class="buttons" id="div" style="margin-top:10px;">
<ul>
<li id="buttonTemplate" class="buttonTemplate">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["varianten_new"] ?></div>
</button>
</li>
</ul>
</div>
</form>
</fieldset>
<?php
}
?>

<?php

$sql = 
 "
 SELECT id, additionaltext, kind FROM s_articles_details WHERE articleID={$_GET["article"]}
 AND kind = 1
 ORDER BY position ASC
 ";


 $getVariants = mysql_query($sql);
 $getVariants = mysql_fetch_assoc($getVariants);

?>
   
<?php
if (!$variantError){
?>

<div class="showthis">
<a class="ico package_green"></a><strong><?php echo $sLang["articles"]["varianten_data"] ?>: <?php echo $getVariants['additionaltext'] ?></strong>       
</div>
   <?php
}
?>

<?php
$sql = 
 "
 SELECT id, additionaltext, kind FROM s_articles_details WHERE articleID={$_GET["article"]}
 AND kind = 2
 ORDER BY position ASC
 ";


 $getVariants = mysql_query($sql);
 
 ?>
 
 

<?php
$numberVariants = mysql_num_rows($getVariants);

if ($numberVariants){
// Ausgabe suppliers
?>
<form name="changeOrder" action="<?php echo $_SERVER["PHP_SELF"] ?>?article=<?php echo $_GET["article"] ?>" method="POST">
<fieldset style="margin: 0 0 0 0; padding:0 0 0 0;">
 <table width="100%"  border="0" cellpadding="2" cellspacing="1" bordercolor="#CCCCCC">
 
   <tr style="background: url(../../../backend/img/default/window/bg_table_header.gif) repeat-x; height:22px;">
         
         <td  width="50%" class="th_bold"><?php echo $sLang["articles"]["varianten_variant"] ?></td>          
         <td  width="50%" class="th_bold"><?php echo $sLang["articles"]["varianten_options"] ?></td>
   </tr>
   </table>
   <ul id="sorts">
<?php

// =================================
	while ($variant=mysql_fetch_array($getVariants))
	{
		$i++;
		$comma = $i==$numberLinks ? "" : ",";?>
		<li class="sortme">
		<span class="handle" style="cursor:move">
			<span style="float:left;width:50%">
            <?php echo $variant['additionaltext'] ?>
            </span>
            <?php if ($variant["kind"]!=1) { ?>
				<a class="ico delete" style="cursor:pointer" onclick="deleteLink(<?php echo $variant["id"] ?>,'<?php echo $variant["additionaltext"] ?>')"></a><a class="ico pencil" style="cursor:pointer" onclick="window.location='artikeln1.inc.php?variante=<?php echo $variant["id"]?>&article=<?php echo $_GET["article"] ?>&edit=<?php echo $variant["id"]?>'"></a> <?php
			} ?>
        <?php
		echo "<input type=\"hidden\" name=\"posvariante[]\" value=\"{$variant["id"]}\">";
        ?>
        </span> 
        </li>
		<?php
	// =================================
	} // for every supplier
	// =================================
	
// =================================
?></ul>

</fieldset>
<div class="buttons" id="div" style="margin-top:10px;">
<ul>
<li id="buttonTemplate" class="buttonTemplate">
<button type="submit" value="send" class="button">
<div class="buttonLabel"><?php echo $sLang["articles"]["varianten_Save_positions"] ?></div>
</button>
</li>
</ul>
</div>
</form>
<?php
} 

?>

   
</body>
</html>