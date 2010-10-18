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

if (!$_GET["article"]) die("No Article");

// *****************
// SPEICHERN DER HERSTELLER
// ===========================================
if ($_POST["sOrdernumber"]){
	
	// Alle Bedingungen erfüllt??
	// Check if article exists
	$queryArticle = mysql_query("
	SELECT id FROM s_articles_details WHERE ordernumber='{$_POST["sOrdernumber"]}'
	");
	
	
	
	
	if (@mysql_num_rows($queryArticle)){
		// Hinzufügen
		$insertLink = mysql_query("
		INSERT INTO s_articles_relationships (articleID, relatedarticle)
		VALUES ({$_GET["article"]},'{$_POST["sOrdernumber"]}')
		");
		if ($insertLink || $updateLink){
			$sInform = $sLang["articles"]["cross_Link_has_been_successfully_saved"];
			
			if ($_POST["sConnect"]){
				// Get ordernumber by id 
				
				$sql = "
				SELECT ordernumber FROM s_articles_details WHERE kind = 1 AND articleID = {$_GET["article"]}
				";
				$getOrdernumber = mysql_query($sql);
				$getOrdernumber = mysql_result($getOrdernumber,0,"ordernumber");
				//die($getOrdernumber."#");
				// Get id by ordernumber
				$getID = mysql_query("
				SELECT articleID FROM s_articles_details WHERE ordernumber = '{$_POST["sOrdernumber"]}'
				");
				$getID = mysql_result($getID,0,"articleID");
				
				if ($getOrdernumber && $getID){
					// Doing relationship reverse
					$sql = "
					INSERT INTO s_articles_relationships (articleID, relatedarticle)
					VALUES ($getID,'{$getOrdernumber}')
					";
					
					$insertLink = mysql_query($sql);
				}
		}
		}else {
			echo $sLang["articles"]["cross_Link_save_failed"]; die(mysql_error());
		}
		
	}else {
			$sError = $sLang["articles"]["cross_article_not_found"];
	}
}
// ===========================================
// Delete?
if ($_GET["delete"]){
	
	$abfrage = mysql_query("
	SELECT articleID,relatedarticle AS ordernumber FROM s_articles_relationships WHERE id = {$_GET["delete"]}
	");
	$id = mysql_result($abfrage,0,"articleID");
	$number = mysql_result($abfrage,0,"ordernumber");
	
	$sql = "
	SELECT ordernumber FROM s_articles_details WHERE kind = 1 AND articleID = $id
	";
	
	$getOrdernumber = mysql_query($sql);
	$getOrdernumber = mysql_result($getOrdernumber,0,"ordernumber");
	$getID = mysql_query("
	SELECT articleID FROM s_articles_details WHERE ordernumber = '$number'
	");
	$getID = mysql_result($getID,0,"articleID");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_relationships WHERE id=".$_GET["delete"]."
	");
	$abfrage = mysql_query("
	DELETE FROM s_articles_relationships WHERE articleID = $getID AND relatedarticle = '$getOrdernumber'
	");
	if ($abfrage){
		$sInform = $sLang["articles"]["cross_link_deleted"];
	}else {
		echo $sLang["articles"]["cross_link_delet_failed"]."<br>".die(mysql_error());
	}
}

// For similar-relationships

if ($_POST["sOrdernumberSimilar"]){
	
	// Alle Bedingungen erfüllt??
	// Check if article exists
	$queryArticle = mysql_query("
	SELECT id FROM s_articles_details WHERE ordernumber='{$_POST["sOrdernumberSimilar"]}'
	");
	
	
	
	if (@mysql_num_rows($queryArticle)){
		// Hinzufügen
		$insertLink = mysql_query("
		INSERT INTO s_articles_similar (articleID, relatedarticle)
		VALUES ({$_GET["article"]},'{$_POST["sOrdernumberSimilar"]}')
		");
		if ($insertLink || $updateLink){
			$sInform = $sLang["articles"]["cross_Similar_article_has_been_successfully_saved"];
			if ($_POST["sConnect"]){
				// Get ordernumber by id 
				
				$sql = "
				SELECT ordernumber FROM s_articles_details WHERE kind = 1 AND articleID = {$_GET["article"]}
				";
				$getOrdernumber = mysql_query($sql);
				$getOrdernumber = mysql_result($getOrdernumber,0,"ordernumber");
				//die($getOrdernumber."#");
				// Get id by ordernumber
				$getID = mysql_query("
				SELECT articleID FROM s_articles_details WHERE ordernumber = '{$_POST["sOrdernumberSimilar"]}'
				");
				$getID = mysql_result($getID,0,"articleID");
				
				if ($getOrdernumber && $getID){
					// Doing relationship reverse
					$sql = "
					INSERT INTO s_articles_similar (articleID, relatedarticle)
					VALUES ($getID,'{$getOrdernumber}')
					";
					
					$insertLink = mysql_query($sql);
				}
			}
		}else {
			echo $sLang["articles"]["cross_Similar_article_save_failed"]; die(mysql_error());
		}
		
	}else {
			$sError = $sLang["articles"]["cross_article_not_found"];
	}
}
// ===========================================
// Delete?
if ($_GET["deleteSimilar"]){
	
	
	$abfrage = mysql_query("
	SELECT articleID,relatedarticle AS ordernumber FROM s_articles_similar WHERE id = {$_GET["deleteSimilar"]}
	");
	$id = mysql_result($abfrage,0,"articleID");
	$number = mysql_result($abfrage,0,"ordernumber");
	
	$sql = "
	SELECT ordernumber FROM s_articles_details WHERE kind = 1 AND articleID = $id
	";
	
	$getOrdernumber = mysql_query($sql);
	$getOrdernumber = mysql_result($getOrdernumber,0,"ordernumber");
	$getID = mysql_query("
	SELECT articleID FROM s_articles_details WHERE ordernumber = '$number'
	");
	$getID = mysql_result($getID,0,"articleID");
	
	$abfrage = mysql_query("
	DELETE FROM s_articles_similar WHERE id=".$_GET["deleteSimilar"]."
	");
	$abfrage = mysql_query("
	DELETE FROM s_articles_similar WHERE articleID = $getID AND relatedarticle = '$getOrdernumber'
	");
	
	if ($abfrage){
		$sInform = $sLang["articles"]["cross_Similar_article_deleted"];
	}else {
		echo $sLang["articles"]["cross_Similar_article_cant_be_deleted"]."<br>".die(mysql_error());
	}
}

//Wert für Bundledarstellung laden 
$sArticleID = intval($_GET["article"]); 
$sql = sprintf(" 
	SELECT `crossbundlelook` 
	FROM `s_articles` 
	WHERE `id` = %d 
", $sArticleID); 
$sqlQ = mysql_query($sql); 

$sBundleLockValue = mysql_result($sqlQ, 0, 'crossbundlelook'); 
$sBundleLockValue = empty($sBundleLockValue) ? "" : "checked";

//Lizenzüberprüfung
$sBUNDLE = $sCore->sCheckLicense("","",$sCore->sLicenseData["sBUNDLE"]) ? true : false;
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

<title>Links</title>

</head>

<body>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script> 
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	//console.log(sFunction);
	switch (sFunction){
		case "deleteArticle":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?article=<?php echo $_GET["article"]?>&delete="+sId;
			break;
		case "deleteArticleSimilar":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?article=<?php echo $_GET["article"]?>&deleteSimilar="+sId;
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

function deleteArticle(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["cross_link_with"] ?>"'+text+'" <?php echo $sLang["articles"]["cross_want_to_Delete"] ?>',window,'deleteArticle',ev);
	}
function deleteArticleSimilar(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["articles"]["cross_Similar_article_link_with"] ?> "'+text+'" <?php echo $sLang["articles"]["cross_want_to_Delete"] ?>',window,'deleteArticleSimilar',ev);
	}
window.onload = function(){
	<?php
		if ($sInform){
			echo "parent.parent.Growl('$sInform');";
		}
		if ($sError){
			echo "parent.Growl('$sError');";
			// Das Fenster shaken
			echo "parent.parent.sWindows.focus.shake(50);";
		}
	?>
	
};

function changeBundleLookValue(checkbox) 
{ 
	<?php
	if(!$sBUNDLE) {
		echo "alert('FEHLER: Bundle-Modul nicht lizenziert!');";
		echo "return;";
	}
	?>
	var value = checkbox.checked; 
	Ext.Ajax.request({ 
	     url: '../../../backend/ajax/setCrosssellingData.php' 
	    ,params: {sBundleLook: value, articleID: '<?php echo $_REQUEST['article']; ?>'} 
	    ,success: function(o, p){ 
	            if("SUCCESS" == o.responseText) 
	            { 
	                    if(value == true) 
	                    parent.parent.Growl('Die Bundledarstellung wurde aktiviert'); 
	                    else 
	                    parent.parent.Growl('Die Bundledarstellung wurde deaktiviert'); 
	            }else{ 
	                    alert("Ajax-Fehler: Die Einstellung 'Bunldedarstellung' konnte nicht gespeichert werden!"); 
	            } 
	    } 
	    ,failure: function(){ 
	            alert("Ajax-Fehler: Die Einstellung 'Bunldedarstellung' konnte nicht gespeichert werden!"); 
	    } 
	}); 
}
</script>



<fieldset style="margin-top: -20px;">
<legend><a class="ico help"></a> <?php echo $sLang["articles"]["cross_tip"] ?></legend>
<?php echo $sLang["articles"]["cross_Here_you_have_the_possibility"] ?>
</fieldset>

		
<fieldset>
<legend><?php echo $sLang["articles"]["cross_Accessories-Article"] ?></legend>

	<form method="POST" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]?>">
	<div style="width:500px; height: 30px;">					
		<ul>		
		<li>
			<label style="width:90px; padding-left:20px;text-align:left" for="name">Bundledarstellung:</label>
			<input name="sBundleLook" type="checkbox" id="sBundleLook" style='margin-left:0;' onchange="changeBundleLookValue(this);" <?php echo $sBundleLockValue; if(!$sBUNDLE) echo " disabled" ?>/>
			<?php if(!$sBUNDLE) { ?>
			<span style="font-weight:bold;color:#E3001B;position:relative;top:3px;left:4px;">(Bundle-Modul Lizenz erforderlich)</span>
			<?php } ?>
		</li>
		<li class="clear"/>
		<li>
			<label style="width:90px; padding-left:20px;text-align:left" for="name">Gegenseitig zuweisen:</label>
			<input name="sConnect" type="checkbox" id="sBundleLook" style='margin-left:0;' />
		</li>
		<li class="clear"/>
		<li><label style="width:90px; padding-left:20px;text-align:left" for="name"><?php echo $sLang["articles"]["cross_ordernumber"] ?></label>
		<input name="sOrdernumber" type="text" id="email" class="w75" value="<?php echo $userMain["email"] ?>" />
		<div class="buttons" id="buttons" style="float:left;">
			<ul>
			<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
			<button type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["articles"]["cross_add"] ?></div></button>
			</li>	
			</ul>
		</div>  
		</li>
		<li class="clear"/>
		</ul>
	</div>
	</form>
<div class="clear"></div>
		<?php
		$sql = "
					SELECT DISTINCT s_articles_relationships.id AS id, s_articles.name
		FROM s_articles, s_articles_details, s_articles_relationships
		WHERE s_articles_relationships.articleID = {$_GET["article"]}
		AND s_articles.id = s_articles_details.articleID
		AND s_articles_details.ordernumber = s_articles_relationships.relatedarticle
					";
					$getArticles = mysql_query($sql);
		if (@mysql_num_rows($getArticles)){
		?>
		
 		<fieldset class="grey" style="margin:0;padding:0 0 0 0;">
		<table cellpadding="2" cellspacing="2" width="100%">
		<tr>
		<td class="th_bold"><?php echo $sLang["articles"]["cross_articlename"] ?></td>
		<td class="th_bold"><?php echo $sLang["articles"]["cross_options"] ?></td>
		</tr>
		<?php	
			// Query Related-Articles
			
			while ($relationship = mysql_fetch_array($getArticles)){
		?>
		<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:40px;border-bottom: 1px solid #a0a0a0;">
		<td><?php echo $relationship["name"]?></td>
		<td><a style="cursor:pointer" class="ico delete" onclick="deleteArticle(<?php echo $relationship["id"]?>,'<?php echo preg_replace("/[^a-zA-Z ]/","",$relationship["name"]) ?>')"></a></td>
		</tr>
		<?php
			}
		?>
		
		</table>
		</fieldset>
		<?php
		}
		?>

</fieldset>	



<fieldset>
<legend><?php echo $sLang["articles"]["cross_similar_articles"] ?></legend>

	<form method="POST" id="ourFormSimilar" action="<?php echo $_SERVER["PHP_SELF"]."?article=".$_GET["article"]?>">
	<div style="width:500px; height: 30px;">					
	<ul>
	<li>
			<label style="width:90px; padding-left:20px;text-align:left" for="name">Gegenseitig zuweisen:</label>
			<input name="sConnect" type="checkbox" id="sBundleLook" style='margin-left:0;' />
		</li>
		<li class="clear"/>
		<li><label style="width:90px; padding-left:20px;text-align:left" for="name"><?php echo $sLang["articles"]["cross_ordernumber"] ?></label>
		<input name="sOrdernumberSimilar" type="text" id="email" class="w75" value="<?php echo $userMain["email"] ?>" />
		<div class="buttons" id="buttons" style="float:left;">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate" style="float:left;margin-left:10px;">
				<button type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["articles"]["cross_add"] ?></div></button>
				</li>	
			</ul>
		</div>  
		</li>
		<li class="clear"/>
	</ul>
	</div>					

	</form>	

<div class="clear"></div>
<?php
$sql = "
			SELECT DISTINCT s_articles_similar.id AS id, s_articles.name
FROM s_articles, s_articles_details, s_articles_similar
WHERE s_articles_similar.articleID = {$_GET["article"]}
AND s_articles.id = s_articles_details.articleID
AND s_articles_details.ordernumber = s_articles_similar.relatedarticle
			";
			$getArticles = mysql_query($sql);
if (@mysql_num_rows($getArticles)){
?>
<fieldset class="grey" style="margin:0;padding:0 0 0 0;">
		<table cellpadding="2" cellspacing="2" width="100%">
		<tr>
		<td class="th_bold"><?php echo $sLang["articles"]["cross_articlename"] ?></td>
		<td class="th_bold"><?php echo $sLang["articles"]["cross_options"] ?></td>
		</tr>
		<?php	
			// Query Related-Articles
			
			while ($relationship = mysql_fetch_array($getArticles)){
		?>
		<tr style="background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x; height:40px;border-bottom: 1px solid #a0a0a0;">
		<td><?php echo $relationship["name"]?></td>
		<td><a style="cursor:pointer" class="ico delete" onclick="deleteArticleSimilar(<?php echo $relationship["id"]?>,'<?php echo preg_replace("/[^a-zA-Z ]/","",$relationship["name"]) ?>')"></a></td>
		</tr>
		<?php
			}
		?>
		
		</table>
		</fieldset>
<?php
}
?>

</fieldset>	
		
		
		
		
		
</body>
</html>