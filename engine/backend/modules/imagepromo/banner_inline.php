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
// *****************


if (!$_GET["id"]) die($sLang["banner"]["banner_no_category_selected"]);
$queryCategory = mysql_query("
SELECT * FROM s_categories WHERE id={$_GET["id"]}
");

if (@mysql_num_rows($queryCategory)){
	$queryCategory = mysql_fetch_array($queryCategory);
}else{
	unset($queryCategory);
	$queryCategory["id"] = 1;
	$queryCategory["description"] = $sLang["banner"]["banner_home"];
	
}

if ($_GET["delete"]){
	$queryBanner = mysql_query("SELECT img FROM s_emarketing_banners WHERE id={$_GET["delete"]}");
	$filename = mysql_result($queryBanner,0,"img");
	unlink("../../../../images/banner/".$filename);
	$deleteBanner = mysql_query("DELETE FROM s_emarketing_banners WHERE id={$_GET["delete"]}");
	if ($deleteBanner){
		$sInform = $sLang["banner"]["banner_banner_deleted"];
	}else {
		$sError = $sLang["banner"]["banner_banner_cant_be_deleted"];
	}
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script type="text/javascript" src="js/calendar.js"></script>
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<link href="js/calendar.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<style>
div.col2 {
	width:auto;
	float:none;
}
</style>
<head>
<?php
function upload($field){
	$filename = $_FILES[$field]['name'];
	$filename = strtolower($filename);
	
	// Datei-Endung
	$filename = explode(".", $filename);
	$filenameext = strtolower($filename[count($filename)-1]);
	if ($filenameext!="gif" && $filenameext!="jpg" && $filenameext!="png" && $filenameext!="jpeg" && $filenameext!="swf"){
		//echo $filenameext;
		return "WRONG FILE";
	}
	$filename = $filename[0];
	$filename = preg_replace("/[^a-zA-Z0-9]/","",$filename);
	// Random-Part for permit overwrite of existing files
	// (Article-ID | Download-Id)
	$filename = $filename."-".$_GET["id"].rand(0,10000).".".$filenameext;
	
		
	if (move_uploaded_file($_FILES[$field]['tmp_name'], "../../../../images/banner/".$filename)){
		chmod("../../../../images/banner/".$filename,0777);
		return array("filename"=>$filename,"ext"=>$filenameext);
	}else {
		return "ERROR";
	}	
}

function makeProperDate($date){
	$date = explode(".",$date);
	return $date[2]."-".$date[1]."-".$date[0];
}

if($_POST["sAction"]=="saveLive"){
	
	if ($_POST["sBannerFrom2"]){
		$_POST["sBannerFrom2"] = makeProperDate($_POST["sBannerFrom2"])." ".$_POST["sBannerFromTime2"];
	}
	if ($_POST["sBannerTo2"]){
		$_POST["sBannerTo2"] = makeProperDate($_POST["sBannerTo2"])." ".$_POST["sBannerToTime2"];;
	}
		
	if (!empty($_POST['sLiveshoppingID'])) {
		if ($_GET["edit"]){
			mysql_query("
				UPDATE s_emarketing_banners
				SET
				valid_from='{$_POST["sBannerFrom2"]}',
				valid_to='{$_POST["sBannerTo2"]}'
				WHERE id={$_GET["edit"]}
			");
		}else{
			mysql_query("
				INSERT INTO s_emarketing_banners
				(description, valid_from, valid_to, liveshoppingID, categoryID)
				VALUES (
				'{$_POST["sBannerName"]}',
				'{$_POST["sBannerFrom2"]}',
				'{$_POST["sBannerTo2"]}',
				'{$_POST["sLiveshoppingID"]}',
				'{$_GET["id"]}'
				)
			");
		}
	}
	
		
}elseif ($_POST["sAction"]=="saveBanner"){
	if (!$_POST["sBannerName"]){
		$error[] = $sLang["banner"]["banner_banner_title"];
	}
	if (!$_FILES["sBannerFile"]["tmp_name"] && !$_GET["edit"]){
		$error[] = $sLang["banner"]["banner_banner_graphic"];
	}
	
	if (!count($error)){
		// Transfer Image
		if ($_FILES["sBannerFile"]["tmp_name"]){
			$result = upload("sBannerFile");
			// Delete old banner r302
			if (!empty($_GET["edit"])){
			$queryBanner = mysql_query("SELECT img FROM s_emarketing_banners WHERE id={$_GET["edit"]}");
				$filename = mysql_result($queryBanner,0,"img");
				unlink("../../../../images/banner/".$filename);
			}
		}else {
			$result = "";
		}
		if ($result=="WRONG FILE"){
			$error[] = $sLang["banner"]["banner_wrong_file_format"];
		}else if ($result=="ERROR"){
			$error[] = $sLang["banner"]["banner_error_during_upload"];
		}else {
			if ($_POST["sBannerFrom"]){
				$_POST["sBannerFrom"] = makeProperDate($_POST["sBannerFrom"])." ".$_POST["sBannerFromTime"];
			}
			if ($_POST["sBannerTo"]){
				$_POST["sBannerTo"] = makeProperDate($_POST["sBannerTo"])." ".$_POST["sBannerToTime"];;
			}
			// Banner einfügen 
			if ($_GET["edit"]){
				
				if ($result){
					$insertBanner = mysql_query("
					UPDATE s_emarketing_banners
					SET description='{$_POST["sBannerName"]}',
					valid_from='{$_POST["sBannerFrom"]}',
					valid_to='{$_POST["sBannerTo"]}',
					img='{$result["filename"]}',
					link='{$_POST["sBannerLink"]}',
					link_target='{$_POST["sBannerTarget"]}',
					extension='{$result["ext"]}'
					WHERE id={$_GET["edit"]}
					");
				}else {
					$sql = "
					UPDATE s_emarketing_banners
					SET description='{$_POST["sBannerName"]}',
					valid_from='{$_POST["sBannerFrom"]}',
					valid_to='{$_POST["sBannerTo"]}',
					link='{$_POST["sBannerLink"]}',
					link_target='{$_POST["sBannerTarget"]}'
					WHERE id={$_GET["edit"]}
					";
					
					$insertBanner = mysql_query($sql);
				}
			}else {
				$insertBanner = mysql_query("
				INSERT INTO s_emarketing_banners
				(description, valid_from, valid_to, img, link, link_target, categoryID, extension)
				VALUES (
				'{$_POST["sBannerName"]}',
				'{$_POST["sBannerFrom"]}',
				'{$_POST["sBannerTo"]}',
				'{$result["filename"]}',
				'{$_POST["sBannerLink"]}',
				'{$_POST["sBannerTarget"]}',
				{$_GET["id"]},
				'{$result["ext"]}'
				)
				");
			
			} // Einfügen
				if ($insertBanner){
					$sInform =  $sLang["banner"]["banner_saved"];
				}else {
					$sError =  $sLang["banner"]["banner_error"]." ".mysql_error();
				}
		} // Datei okay
	} // Keine Fehler
}

if ($_GET["edit"]){
	$queryBanner = mysql_query("SELECT * FROM s_emarketing_banners WHERE id={$_GET["edit"]}");
	if (@mysql_num_rows($queryBanner)){
		$queryBanner = mysql_fetch_array($queryBanner);
		
		$_POST["sBannerName"] = $queryBanner["description"];
		$_POST["sBannerLink"] = $queryBanner["link"];
		$_POST["sBannerTarget"] = $queryBanner["link_target"];
		$_POST["sLiveshoppingID"] = $queryBanner["liveshoppingID"];
		
		
		$_POST["sBannerFromTime"] = date("H:i:s",strtotime($queryBanner["valid_from"]));
		$_POST["sBannerToTime"] = date("H:i:s",strtotime($queryBanner["valid_to"]));
		
		if ($queryBanner["valid_from"]){
			$time = strtotime($queryBanner["valid_from"]);
			if ($time == 0){
				$queryBanner["valid_from"] = "";
			}else {
				$queryBanner["valid_from"] = date("d.m.Y",$time);
			}
		
		}
		if ($queryBanner["valid_to"]){
			$time = strtotime($queryBanner["valid_to"]);
			if ($time == 0){
				$queryBanner["valid_to"] = "";
			}else {
				$queryBanner["valid_to"] = date("d.m.Y",$time);
			}
		}
		$_POST["sBannerFrom"] = $queryBanner["valid_from"];
		
		$_POST["sBannerTo"] = $queryBanner["valid_to"];
		
	}
}
?>
<body>
<div class="col2" style="margin-top:0px">
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

function deleteBanner(ev,text){
		parent.parent.sConfirmationObj.show('<?php echo $sLang["banner"]["banner_should_the_banner"] ?> "'+text+'" <?php echo $sLang["banner"]["banner_really_be_deleted"] ?>',window,'deleteBanner',ev);
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

function sTypChange(typeID) {
	if(2==typeID) {
		$('banner_fields').setStyle('display', 'none');
		$('liveshopping_fields').setStyle('display', 'block');
	}else{
		$('banner_fields').setStyle('display', 'block');
		$('liveshopping_fields').setStyle('display', 'none');
	}
}
function sChangeCombo()
{
	var lvCombo = Ext.getCmp('sExtLiveshopping');
	var lvComboVal = lvCombo.getValue();
	var lvComboRowVal = lvCombo.getRawValue();
	if(lvComboVal == parseInt(lvComboVal) && lvComboVal!='') {
		$('live_submit').setStyle('display', 'block');
		$('sLiveshoppingID').value = lvComboVal;
		Ext.getCmp('sBannerName').setValue(lvComboRowVal);
	}else{
		$('live_submit').setStyle('display', 'none');
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
		<?php if(!empty($_POST['sBannerName'])) echo sprintf(",value: '%s'", $_POST['sBannerName']); ?>
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
	
	<?php
	if(!empty($_POST["sLiveshoppingID"])) {
		echo "sTypChange(2);";
	}
	?>
});
</script>
	<?php
	if ($_GET["edit"]){
	?>
	
		<div class="buttons" id="buttons" style="margin-left:5px;">
		<ul>		
			
		<li id="buttonTemplate" class="buttonTemplate"><a href="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"] ?>" class="bt_icon image" value="send" style="text-decoration:none;"><?php echo $sLang["banner"]["banner_new_banner"] ?></a></li>	
		</ul>
		</div>
	<div class="clear" style="height:40px;"></div>
	
	<?php
	}
	?>
	


	<div class="clear"></div>
<form enctype="multipart/form-data" method="POST" name="ourForm" id="ourForm" action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
<input name="sAction" value="saveBanner" type="hidden">
<fieldset class="col2_cat2" style="margin:-21px 5px 30px;">
<legend><a class="ico image"></a> Banner in Kategorie: <?php echo $queryCategory["description"]?></legend>
<!-- error Msg -->
<?php
if (count($error)){
	echo $sLang["banner"]["banner_please_complete_the_following_fields"];
	echo "<ul>";
	foreach ($error as $errorMsg){
		echo "<li>$errorMsg</li>";
	}
	echo "</ul>";
}
?>
<!-- /error Msg -->	
	<?php if((empty($_REQUEST['edit']) && !isset($_POST['sAction'])) && $sCore->sCheckLicense("","",$sCore->sLicenseData["sLIVE"]) ) { ?>
	<ul>
	<li><label class="small">Typ:</label>
		<select name="sType" onchange="sTypChange(this.value);">
			<option value="1">Banner</option>
			<option value="2">Liveshopping</option>
		</select>
	</li>	
	<li class="clear"/>
	</ul>
	<?php } ?>
	
	<div id="banner_fields">
		<ul>
		<li><label class="small"><?php echo $sLang["banner"]["banner_title"] ?></label>
			<input name="sBannerName" type="text" id="email" class="w150" value="<?php echo $_POST["sBannerName"] ?>" />
		</li>
		<li class="clear"/>
		<li><label class="small"><?php echo $sLang["banner"]["banner_link"] ?></label>
			<input name="sBannerLink" type="text" id="link" class="w150" value="<?php echo $_POST["sBannerLink"] ?>" />
		</li>
		<li class="clear"/>
		<li><label class="small"><?php echo $sLang["banner"]["banner_linktarget"] ?></label>
			<select name="sBannerTarget" class="w150">
			<option value="_parent" <?php echo $_POST["sBannerTarget"]=="_parent" ? "selected" : ""?>><?php echo $sLang["banner"]["banner_shopware"] ?></option>
			<option value="_blank" <?php echo $_POST["sBannerTarget"]=="_blank" ? "selected" : ""?>><?php echo $sLang["banner"]["banner_extern"] ?></option>
			
			</select>
		</li>
		<li class="clear"/>
			<li><label class="small"><?php echo $sLang["banner"]["banner_valid_from"] ?></label>
			<input class="w80" id="sBannerFrom" name="sBannerFrom" value="<?php echo $_POST["sBannerFrom"]?>" onclick="displayDatePicker('sBannerFrom', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerFrom', this, 'dmy', '.');"></a>
			<input type="text" class="w80" name="sBannerFromTime" value="<?php echo $_POST["sBannerFromTime"] ? $_POST["sBannerFromTime"] : "00:00:00" ?>">
		</li>
		<li class="clear"/>
				<li><label class="small"><?php echo $sLang["banner"]["banner_valid_until"] ?></label>
			<input class="w80" id="sBannerTo" name="sBannerTo" value="<?php echo $_POST["sBannerTo"]?>" onclick="displayDatePicker('sBannerTo', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerTo', this, 'dmy', '.');"></a>
			<input type="text" class="w80" name="sBannerToTime" value="<?php echo $_POST["sBannerToTime"] ? $_POST["sBannerToTime"] : "00:00:00" ?>">
		</li>
		<li class="clear"/>
		<li><label class="small">Grafik:</label>
			<input name="sBannerFile" type="file" id="email" class="w80 h24" style="width:auto;" value="<?php echo $userMain["email"] ?>" />
		</li>
		
		<li class="clear"/>
				<?php
				if ($queryBanner["img"] && !preg_match("/\.swf/",$queryBanner["img"])){
					echo "<li><img src=\"../../../../images/banner/{$queryBanner["img"]}\" height=\"70\" width=\"200\"></li><li class=\"clear\"/>";
				}
			?>
		</ul>
		<div class="buttons" id="buttons">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate">
				<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel"><?php echo $sLang["banner"]["banner_save_banner"] ?></div></button>
				</li>	
			</ul>
		</div>
	</div><!--banner_fields ENDE-->
	</form>
	<form method="POST" id="liveForm"  action="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]?>&edit=<?php echo $_GET["edit"]?>">
	<input name="sAction" value="saveLive" type="hidden">
		
	<div id="liveshopping_fields" style="display:none;">
		<ul>
		<div style="display:<?php echo !empty($_GET['edit']) || isset($_POST['sAction']) ? 'none' : 'block';?>">
		<li><label class="small">Artikelnummer:</label>
			<div id="sOrdernumberRender" style="float:left;"></div>
			<div id="sBannerNameRender" style="float:left;display:none;"></div>
		</li>
		<li class="clear"/>
		</div>
		<li><label class="small">Liveshopping:</label>
			<div id="sLiveshoppingRender" style="float:left;"></div>
			<input type="hidden" id="sLiveshoppingID" name="sLiveshoppingID" value="<?php echo $_POST['sLiveshoppingID']; ?>"/>
		</li>
		<li class="clear"/>
			<li><label class="small"><?php echo $sLang["banner"]["banner_valid_from"] ?></label>
			<input class="w80" id="sBannerFrom2" name="sBannerFrom2" value="<?php echo $_POST["sBannerFrom"]?>" onclick="displayDatePicker('sBannerFrom2', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerFrom2', this, 'dmy', '.');"></a>
			<input type="text" class="w80" name="sBannerFromTime2" value="<?php echo $_POST["sBannerFromTime"] ? $_POST["sBannerFromTime"] : "00:00:00" ?>">
		</li>
		<li class="clear"/>
				<li><label class="small"><?php echo $sLang["banner"]["banner_valid_until"] ?></label>
			<input class="w80" id="sBannerTo2" name="sBannerTo2" value="<?php echo $_POST["sBannerTo"]?>" onclick="displayDatePicker('sBannerTo2', this, 'dmy', '.');"><a class="ico calendar"  onclick="displayDatePicker('sBannerTo2', this, 'dmy', '.');"></a>
			<input type="text" class="w80" name="sBannerToTime2" value="<?php echo $_POST["sBannerToTime"] ? $_POST["sBannerToTime"] : "00:00:00" ?>">
		</li>
		<li class="clear"/>
		</ul>
		
		<div id="live_submit" style="display:<?php echo !empty($_GET['edit']) || isset($_POST['sAction']) ? 'block' : 'none';?>;">
		<div class="buttons" id="buttons">
			<ul>
				<li id="buttonTemplate" class="buttonTemplate">
				<button onClick="$('ourForm').submit();" type="submit" value="send" class="button"><div class="buttonLabel">Liveshopping speichern</div></button>
				</li>	
			</ul>
		</div>
		</div>
	</div>
	</form>
	<!--liveshopping_fields ENDE-->
</fieldset>

<fieldset class="col2_cat2" style="">
<legend><a class="ico help"></a> <?php echo $sLang["banner"]["banner_note"] ?></legend>
<p><?php echo $sLang["banner"]["banner_please_make_sure"] ?></p>
</fieldset>
<fieldset class="col2_cat2">
<legend><?php echo $sLang["banner"]["banner_already_assigned_banner"] ?></legend>
<table cellpadding="0" cellspacing="0" class="listing">
   <tbody>
   		<?php	
			// Query Related-Articles
			$sql = "
			SELECT id, description, img FROM s_emarketing_banners WHERE categoryID={$_GET["id"]}
			ORDER BY id DESC
			";
			$getArticles = mysql_query($sql);
			while ($relationship = mysql_fetch_array($getArticles)){
		?>
     <tr class="rowcolor2">
       <th class="first-child">
       <?php if (preg_match("/\.swf/",$relationship["img"])){ ?>
       	<strong>Flash-Banner</strong>
       <?php
       } else {
       if (is_file('../../../../images/banner/'.$relationship["img"])){
       ?>
       
       <img src="../../../../images/banner/<?php echo $relationship["img"]?>" height="32" width="64">
       <?php
       }}
       ?>
       </th>
	   <td><?php echo $relationship["description"]?></td>
       <td class="last-child">
		   <a style="cursor:pointer" class="ico delete" onclick="deleteBanner(<?php echo $relationship["id"]?>,'<?php echo $relationship["description"]?>')"></a>
		   <a href="<?php echo $_SERVER["PHP_SELF"]."?id=".$_GET["id"]."&edit=".$relationship["id"]?>" style="cursor:pointer" class="ico pencil"></a>
	  </td>
   </tr>
	 	<?php
			}
		?>
   </tbody>
 </table>
</fieldset>

</div>
</body>
</html>
<?php die(); ?>