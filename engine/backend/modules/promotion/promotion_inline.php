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
//echo "<pre>";
//print_r($_POST);
//die;

if (!$_GET["id"]) die($sLang["promotion"]["promotion_inline_No_category_selected"]);
$queryCategory = mysql_query("
SELECT * FROM s_categories WHERE id={$_GET["id"]}
");

if (@mysql_num_rows($queryCategory)){
	$queryCategory = mysql_fetch_array($queryCategory);
}else{
	unset($queryCategory);
	$queryCategory["id"] = 1;
	$queryCategory["description"] = $sLang["promotion"]["promotion_inline_start"];
	
}

if ($_GET["delete"]){
	$queryBanner = mysql_query("SELECT img FROM s_emarketing_promotions WHERE id={$_GET["delete"]}");
	$filename = mysql_result($queryBanner,0,"img");
	@unlink("../../../../images/banner/".$filename);
	$deleteBanner = mysql_query("DELETE FROM s_emarketing_promotions WHERE id={$_GET["delete"]}");
	if ($deleteBanner){
		$sInform = $sLang["promotion"]["promotion_inline_promotion_deleted"];
	}else {
		$sError = $sLang["promotion"]["promotion_inline_promotion_cant_be_deleted"];
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="../../../backend/js/moo12-core.js"></script>
<script type="text/javascript" src="../../../backend/js/moo12-more.js"></script>
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
<head>

<?php
function upload($field){
	$filename = $_FILES[$field]['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = strtolower($filename[count($filename)-1]);
	if ($filenameext!="gif" && $filenameext!="jpg" && $filenameext!="png" && $filenameext!="jpeg"){
		echo $filenameext;
		return "WRONG FILE";
	}
	$filename = $filename[0];
	$filename = preg_replace("/[^a-zA-Z0-9]/","",$filename);
	// Random-Part for permit overwrite of existing files
	// (Article-ID | Download-Id)
	$filename = $filename."-".$_GET["id"].rand(0,10000).".".$filenameext;
	
		
	if (move_uploaded_file($_FILES[$field]['tmp_name'], "../../../../images/banner/".$filename)){
		return $filename;
	}else {
		return "ERROR";
	}	
}

function makeProperDate($date){
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
}

if ($_POST["sAction"]=="savePromotion"){
	
	
	if (!$_POST["txtBezeichnung"] && $_POST["mode"]!= "liverand" && $_POST["mode"] != "liverandcat" && $_POST["mode"] != "livefix") $error[] = $sLang["promotion"]["promotion_inline_title"];

	switch ($_POST["mode"]){
		case "fix":
			if (!$_POST["txtbestellnr"]){
				$error[] = $sLang["promotion"]["promotion_inline_ordernumber"];
			}else {
				$queryArticle = mysql_query("
				SELECT id FROM s_articles_details WHERE ordernumber='{$_POST["txtbestellnr"]}'
				");
				if (!@mysql_num_rows($queryArticle)){
					$error[] = $sLang["promotion"]["promotion_inline_article_with_ordernumber"]." \"{$_POST["txtbestellnr"]}\" ".$sLang["promotion"]["promotion_inline_not_found"];
				}
			}
			// Check if Article exists
			break;
		case "gfx":
			if (!$_FILES["probe"]["tmp_name"] && !$_GET["edit"]) $error[] = $sLang["promotion"]["promotion_inline_image"];
			break;
		case "liverand":
			$_POST['txtBezeichnung'] = 'Zufälliger Liveshopping-Artikel';
			break;
		case "liverandcat":
			$_POST['txtBezeichnung'] = 'Zufälliger Liveshopping-Artikel dieser Kategorie';
			break;
	}
	
	if($_POST["mode"] == "livefix" && empty($_GET['edit'])) {
		if(empty($_POST["sLiveshoppingID"])) $error[] = 'Geben Sie einen Liveshopping-Artikel an!';
	}
	
	
	if (!count($error)){
		// Transfer Image
		if ($_FILES["probe"]["tmp_name"]){
			$result = upload("probe");
		}else {
			$result = "";
		}
		if ($result=="WRONG FILE"){
			$error[] = $sLang["promotion"]["promotion_inline_wrong_fileformat"];
		}else if ($result=="ERROR"){
			$error[] = $sLang["promotion"]["promotion_inline_error_during_upload"];
		}else {
			if ($_POST["sBannerFrom"]){
				$_POST["sBannerFrom"] = makeProperDate($_POST["sBannerFrom"]);
			}
			if ($_POST["sBannerTo"]){
				$_POST["sBannerTo"] = makeProperDate($_POST["sBannerTo"]);
			}
			
			if (!$_POST["txtbestellnr"]) $_POST["txtbestellnr"] = 0;
			
			// Banner einfügen 
			if ($_GET["edit"]){
				if ($result){
					$insertPromotion = mysql_query("
					UPDATE s_emarketing_promotions 
					SET description='{$_POST["txtBezeichnung"]}',
					mode='{$_POST["mode"]}',
					ordernumber='{$_POST["txtbestellnr"]}',
					link='{$_POST["txtHyperlink"]}',
					link_target='{$_POST["txtTarget"]}',
					valid_from='{$_POST["sBannerFrom"]}',
					valid_to='{$_POST["sBannerTo"]}',
					img='$result'
					WHERE id={$_GET["edit"]}
					");
				}else {
					$insertPromotion = mysql_query("
					UPDATE s_emarketing_promotions 
					SET description='{$_POST["txtBezeichnung"]}',
					mode='{$_POST["mode"]}',
					ordernumber='{$_POST["txtbestellnr"]}',
					link='{$_POST["txtHyperlink"]}',
					link_target='{$_POST["txtTarget"]}',
					valid_from='{$_POST["sBannerFrom"]}',
					valid_to='{$_POST["sBannerTo"]}'
					WHERE id={$_GET["edit"]}
					");
				}
					
			}else {
				
			
				$insertPromotion = mysql_query("
				INSERT INTO s_emarketing_promotions
				(description, category,mode,ordernumber,link,link_target,valid_from,valid_to, img, liveshoppingID)
				VALUES (
				'{$_POST["txtBezeichnung"]}',
				{$_GET["id"]},
				'{$_POST["mode"]}',
				'{$_POST["txtbestellnr"]}',
				'{$_POST["txtHyperlink"]}',
				'{$_POST["txtTarget"]}',
				'{$_POST["sBannerFrom"]}',
				'{$_POST["sBannerTo"]}',
				'$result',
				'{$_POST["sLiveshoppingID"]}'
				)
				");
			
			} // Einfügen
				if ($insertPromotion){
					$sInform =  $sLang["promotion"]["promotion_inline_promotion_saved"];
				}else {
					echo $sLang["promotion"]["promotion_inline_error"]."".mysql_error();
				}
		} // Datei okay
	} // Keine Fehler
}

if ($_GET["edit"]){
	$queryPromotion = mysql_query("SELECT * FROM s_emarketing_promotions WHERE id={$_GET["edit"]}");
	if (@mysql_num_rows($queryPromotion)){
		$queryPromotion = mysql_fetch_array($queryPromotion);
		
		$_POST["txtBezeichnung"] = $queryPromotion["description"];
		$_POST["mode"] = $queryPromotion["mode"];
		$_POST["txtbestellnr"] = $queryPromotion["ordernumber"];
		$_POST["txtHyperlink"] = $queryPromotion["link"];
		$_POST["txtTarget"] = $queryPromotion["link_target"];
		$_POST["img"] = $queryPromotion["img"];
		
		$_POST["sBannerLink"] = $queryPromotion["link"];
		$_POST["sBannerTarget"] = $queryPromotion["link_target"];
		
		if ($queryPromotion["valid_from"]){
			$from = explode("-",$queryPromotion["valid_from"]);
			$queryPromotion["valid_from"] = $from[2].".".$from[1].".".$from[0];
		}
		if ($queryPromotion["valid_to"]){
			$to = explode("-",$queryPromotion["valid_to"]);
			$queryPromotion["valid_to"] = $to[2].".".$to[1].".".$to[0];
		}
		$_POST["sBannerFrom"] = $queryPromotion["valid_from"];
		$_POST["sBannerTo"] = $queryPromotion["valid_to"];
			
	}

}
?>
<body>

<script>
// Shopware - 2  JS-Wrapper-Code -
function sWrapper(sFunction, sId){
	
	switch (sFunction){
		case "deleteBanner":
			// Redirect
			window.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?id=<?php echo $_GET["id"]?>&delete="+sId;
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

function deletePromotion(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["promotion"]["promotion_inline_should_the_promotion"] ?> "'+text+'" <?php echo $sLang["promotion"]["promotion_inline_really_deleted"] ?>',window,'deleteBanner',ev);
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
<?php
if (count($error)){
	
	echo "<div style=\"background-color:#EE2200;color:#FFF;border:1px solid; margin-top:0px;margin-bottom:25px;padding:10px 10px 10px 10px;font-size:13px;\">";
	echo "<strong>".$sLang["promotion"]["promotion_inline_please_fill_out"]."</strong>
	<ul>";
	foreach ($error as $errorMsg){
		echo "<li>$errorMsg</li>";
	}
	echo "</ul></div>";
}
if (!count($_POST)) $_POST["mode"] = "fix";
?>
<script>
function checkFields(value){
	if (value=='gfx'){ 
		$('additionalFix').setStyle('display','none'); 
		$('additionalPicture').setStyle('display','block'); 
		$('liveshopping_container').setStyle('display','none'); 
		$('ordernumber_container').setStyle('display','none'); 
	} 
	else if (value=='fix'){
		$('additionalFix').setStyle('display','block'); 
		$('additionalPicture').setStyle('display','none'); 
		$('liveshopping_container').setStyle('display','none'); 
		$('ordernumber_container').setStyle('display','none'); 
	}
	else if (value=='livefix'){
		$('additionalFix').setStyle('display','none'); 
		$('additionalPicture').setStyle('display','none'); 
		$('txtBezeichnung_container').setStyle('display','none'); 
		$('liveshopping_container').setStyle('display','block'); 
		$('ordernumber_container').setStyle('display','block'); 
	}
	else if (value=='liverand' || value=='liverandcat'){
		$('additionalFix').setStyle('display','none'); 
		$('additionalPicture').setStyle('display','none'); 
		$('txtBezeichnung_container').setStyle('display','none'); 
		$('liveshopping_container').setStyle('display','none'); 
		$('ordernumber_container').setStyle('display','none'); 
	}
	else { 
		$('additionalFix').setStyle('display','none'); 
		$('additionalPicture').setStyle('display','none'); 
		$('liveshopping_container').setStyle('display','none'); 
		$('ordernumber_container').setStyle('display','none'); 
	} 
}

function sChangeCombo()
{
	var lvCombo = Ext.getCmp('sExtLiveshopping');
	var lvComboVal = lvCombo.getValue();
	var lvComboRowVal = lvCombo.getRawValue();
	if(lvComboVal == parseInt(lvComboVal) && lvComboVal!='') {
		$('sLiveshoppingID').value = lvComboVal;
		$('txtBezeichnung').value = lvComboRowVal;
	}else{
		$('sLiveshoppingID').value = lvComboVal;
	}
}

Ext.onReady(function(){
	new Ext.form.TextField({
		id: 'sOrdernumber',
		renderTo: 'sOrdernumberRender',
		width:200,
		listeners: {'change': function(field){
			Ext.getCmp('sExtLiveshopping').store.load({params:{ordernumber:field.getValue()}});
		}}
	});
	new Ext.form.TextField({
		id: 'sBannerName',
		renderTo: 'sBannerNameRender',
		width:200
	});
	
	new Ext.form.ComboBox({
		renderTo: 'sLiveshoppingRender'
		,id: 'sExtLiveshopping'
		,name: 'sExtLiveshopping'
		,width:183
		,disabled: true
		,listWidth:200
		,mode: 'local'
		<?php if(!empty($_POST['txtBezeichnung'])) echo sprintf(",value: '%s'", $_POST['txtBezeichnung']); ?>
		,triggerAction: 'all'
		,displayField: 'name'
		,valueField: 'id'
		,triggerAction: 'all'
		,typeAhead: true
		,editable: false
		,store: new Ext.data.Store({
	        url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/getLiveByOrdernumber.php',
	        reader: new Ext.data.JsonReader({
	            root: 'data',
	            totalProperty: 'total',
	            id: 'id',
	            fields: [
	                'id','name'
	            ]
	        }),
	        remoteSort: true,
	        listeners: {'load': function(store, rec, options){
	        	var lvCombo = Ext.getCmp('sExtLiveshopping');
	        	lvCombo.clearValue();
	        	if(0 == store.totalLength){
	        		lvCombo.disable();
	        		lvCombo.setValue('Kein Liveshopping vorhanden');
	        	}else if(1 == store.totalLength){
	        		lvCombo.disable();
	        		lvCombo.setValue(store.getAt(0).get('id'));
	        	}else{
	        		lvCombo.enable();
	        	}
	        	sChangeCombo();
	        }}
    	}),
    	listeners: {'select': function(){
    		sChangeCombo();
    	}}
	});
});
</script>
<form action="<?php echo $PHP_SELF."?edit=".$_GET["edit"]."&id=".$_GET["id"]?>" method="post" enctype="multipart/form-data" id="ourForm">
<input type="hidden" name="sAction" value="savePromotion">
<fieldset class="col2_cat2">
<legend>Promotion in Kategorie: <?php echo $queryCategory["description"]?></legend>
<ul>
	<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_Promotion_Art"] ?></label>
		<select name="mode" class="w200" onchange="checkFields(this.value)">
      <option value="fix" <?php if (!$_POST["mode"] || $_POST["mode"]=="fix"){ echo "selected";}?>><?php echo $sLang["promotion"]["promotion_inline_Fixed_Article"] ?></option>
      <option value="random" <?php if ($_POST["mode"]=="random"){ echo "selected"; }?>><?php echo $sLang["promotion"]["promotion_inline_random_article"] ?></option>
      <option value="new" <?php if ($_POST["mode"]=="new"){ echo "selected"; }?>><?php echo $sLang["promotion"]["promotion_inline_new"] ?></option>
      <option value="top" <?php if ($_POST["mode"]=="top"){ echo "selected"; }?>><?php echo $sLang["promotion"]["promotion_inline_top_article"] ?></option>
      <option value="gfx" <?php if ($_POST["mode"]=="gfx"){ echo "selected"; }?>><?php echo $sLang["promotion"]["promotion_inline_own_picture"] ?></option>
      <?php if ($sCore->sCheckLicense("","",$sCore->sLicenseData["sLIVE"])){ ?>
      <option value="livefix" <?php if ($_POST["mode"]=="livefix"){ echo "selected"; }?>>Fester Liveshopping-Artikel</option>
      <option value="liverand" <?php if ($_POST["mode"]=="liverand"){ echo "selected"; }?>>Zufälliger Liveshopping-Artikel</option>
      <option value="liverandcat" <?php if ($_POST["mode"]=="liverandcat"){ echo "selected"; }?>>Zufälliger Liveshopping-Artikel dieser Kategorie</option>
      <?php } ?>
    </select>
	</li>
	<li class="clear"/>
	</ul>
	<div id="txtBezeichnung_container" style="display:<?php if($_POST["mode"] == "liverand" || $_POST["mode"] == "liverandcat" || $_POST["mode"] == "livefix") { echo 'none'; }else{ echo 'block'; };?>">
	<ul>
	<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_title_1"] ?></label>
	<input id="txtBezeichnung" name="txtBezeichnung" type="text" class="w200" value="<?php echo $_POST["txtBezeichnung"]; ?>" />
	</li>
	<li class="clear"/>
	</ul>
	</div>
	<div id="ordernumber_container" style="display:<?php if($_POST["mode"] == "livefix" && empty($_GET['edit'])) { echo 'block'; }else{ echo 'none'; };?>">
	<ul>
	<li><label class="small">Artikelnummer:</label>
		<div id="sOrdernumberRender" style="float:left;"></div>
		<div id="sBannerNameRender" style="float:left;display:none;"></div>
	</li>
	<li class="clear"/>
	</ul>
	</div>
	<div id="liveshopping_container" style="display:<?php if($_POST["mode"] == "livefix") { echo 'block'; }else{ echo 'none'; };?>">
	<ul>
	<li><label class="small">Liveshopping:</label>
		<div id="sLiveshoppingRender" style="float:left;"></div>
		<input type="hidden" id="sLiveshoppingID" name="sLiveshoppingID" value="<?php echo $_POST['sLiveshoppingID']; ?>"/>
	</li>
	<li class="clear"/>
	</ul>
	</div>
	<ul>
		<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_valid_from"]?></label>
<input class="w80" id="sBannerFrom" name="sBannerFrom" value="<?php echo $_POST["sBannerFrom"]?>" onclick="displayDatePicker('sBannerFrom', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerFrom', this, 'dmy', '.');"></a>
	</li>
	<li class="clear"/>
	<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_valid_until"] ?></label>
<input class="w80" id="sBannerTo" name="sBannerTo" value="<?php echo $_POST["sBannerTo"]?>" onclick="displayDatePicker('sBannerTo', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerTo', this, 'dmy', '.');"></a>
	</li>
	<li class="clear"/>
	</ul>
	<div class="clear"></div>
	
	<!-- Für Eigenes Bild-->
	<div class="clear" style="height:50px;"></div>
	<fieldset id="additionalPicture" style="display:<?php echo $_POST["mode"]=="gfx" ? "block" : "none"?>">
	<legend><?php echo $sLang["promotion"]["promotion_inline_options_for_own_image"] ?></legend>
	<ul>
	<li>
	<label class="small"><?php echo $sLang["promotion"]["promotion_inline_own_image"] ?></label>
 	<input name="probe" type="file" class="w200 h24">
          <?php
			if ($_POST["img"]){
			echo "<br />"."<img src=\""."../../../../images/banner/".$_POST["img"]."\">";	
			}
          ?></li>
		  <li class="clear"/>
	<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_link"] ?></label>
	<input name="txtHyperlink" type="text" class="w200" value="<?php echo $_POST["txtHyperlink"]; ?>"></li>
	<li class="clear"/>
	<li><label class="small"><?php echo $sLang["promotion"]["promotion_inline_link_target"] ?></label>
	<select name="txtTarget" class="w150">
       <option value="_parent" <?php echo $_POST["txtTarget"]=="_parent" ? "selected" : ""?>><?php echo $sLang["promotion"]["promotion_inline_shopware"] ?></option>
       <option value="_blank" <?php echo $_POST["txtTarget"]=="_blank" ? "selected" : ""?>><?php echo $sLang["promotion"]["promotion_inline_extern"] ?></option>
    </select>
	</li>
	<li class="clear"/>
	</ul>
	</fieldset>
<!-- /Für Eigenes Bild-->

<!-- Für Fester-Artikel -->
	<div class="clear" style="height:5px;"></div>
	<fieldset id="additionalFix" style="width: 400px; margin:-50px 0 0 0; display:<?php echo $_POST["mode"]=="fix" ? "block" : "none"?>">
	<legend><?php echo $sLang["promotion"]["promotion_inline_Defined_options_for_Article"] ?></legend>
	<ul>
	<li><label><?php echo $sLang["promotion"]["promotion_inline_ordernumber"] ?></label>
	<input name="txtbestellnr" type="text" class="w75" value="<?php echo $_POST["txtbestellnr"]?>" /></li>
	<li class="clear"/>
	</ul>
	</fieldset>
<!-- /Für Fester-Artikel -->

	<div class="buttons" id="buttons" style="margin-top:10px">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel">
			<?php
			if (isset($_GET["edit"])){
			?>
			Promotion bearbeiten
			<?php
			}else {
			?>
			Promotion anlegen
			<?php
			}
			?>
			</div></button>
			</li>	
		</ul>
	</div>
</fieldset>

</form>

	<div class="buttons" id="buttons" style="margin-left: 5px;">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>">
			<button class="button"><div class="buttonLabel"><?php echo $sLang["promotion"]["promotion_inline_new_promotion"] ?></div></button>
			</a></li>	
		</ul>
	</div>
	

<div class="clear" style="height: 40px;"></div>
	
	
<?php
if(isset($_REQUEST['pos_banner']))
{
	foreach ($_REQUEST['pos_banner'] as $key => $value)
	{
		$sql="UPDATE `s_emarketing_promotions` SET `position` = '$key' WHERE `s_emarketing_promotions`.`id` =$value LIMIT 1 ;";
		mysql_query($sql);
	}
}
$queryBanners = mysql_query("
SELECT * FROM s_emarketing_promotions WHERE category={$_GET["id"]} ORDER BY position ASC
");

if (@mysql_num_rows($queryBanners)){
?>

<fieldset class="col2_cat2" style="width:95%">
<legend><?php echo $sLang["promotion"]["promotion_inline_Already_associated_promotions"] ?></legend>
<p><img src="../../../backend/img/default/icons/information.png" style="margin:0 15px 0 0;" /><?php echo $sLang["promotion"]["promotion_inline_Position_changes"] ?></p>
<br />
<script>
window.addEvent('domready', function(){
new Sortables($('sorts'), {
	handles: $$('#sorts .handle')
	});
});
</script>
<style>


#sorts { 
	position: inherit;
	padding: 0px 8px;
}
 
ul#sorts {
	margin: 0;
}
 
li.sortme {
	/*padding: 4px 8px; */
	padding: 10px 0px;
	margin:0;
	cursor: move;
	list-style: none;
	width: 400px;
	float: none;
	position:relative;
	background: url(../../../backend/img/default/window/fieldset_table_bg.gif) repeat-x;
	height: 18px;
	background-position:0px 1px;
	/*border-bottom: 1px solid #dfdfdf;*/

}
 
ul#sorts li {

}
</style>
<form id="ourForm2" action="<?php echo $PHP_SELF."?edit=".$_GET["edit"]."&id=".$_GET["id"]?>" method="post" enctype="multipart/form-data">
<ul id="sorts">
<?php
while ($banner = mysql_fetch_array($queryBanners)){
	// Bestellnummer herausfinden
//	if ($banner["mode"]=="fix"){
//		$queryOrderNr = mysql_query("
//			SELECT name FROM s_articles, s_articles_details 
//			WHERE s_articles_details.ordernumber='{$banner["ordernumber"]}'
//			AND s_articles_details.articleID=s_articles.id
//		");	
//		if (!@mysql_num_rows($queryOrderNr)){
//			echo mysql_error();
//			$banner["description"] = "FEHLER";	
//		}else {
//			$banner["description"] = mysql_result($queryOrderNr,0,"name");
//		}
//	}
	echo "<li class=\"sortme\"><span class=\"handle\"><img src=\"../../../backend/img/default/icons/package_green.png\" style=\"margin:0 15px 0 0;\" />".htmlspecialchars($banner["description"]);
	?>
	<a class="ico delete" style="cursor:pointer; float:right;position:absolute;right:11px;top:12px;" onclick="deletePromotion(<?php echo $banner["id"] ?>,'<?php echo htmlspecialchars(htmlspecialchars($banner["description"],ENT_QUOTES))?>')"></a>
    <a class="ico pencil" style="cursor:pointer; float:right;position:absolute;right:34px;top:12px;" href="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]."&edit=".$banner["id"]?>"></a>

	<?php
	echo "<input type=\"hidden\" name=\"pos_banner[]\" value=\"{$banner["id"]}\"></span></li>";
}?>
</ul>
</form><br />

<div class="buttons" id="buttons">
		<ul>
			<li id="buttonTemplate" class="buttonTemplate">
			<button onClick="$('ourForm2').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["promotion"]["promotion_inline_Position_saved"] ?></div></button>
			</li>	
		</ul>
	</div>


</fieldset>


<?php }?>

</body>
</html>