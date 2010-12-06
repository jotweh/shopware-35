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


if ($_REQUEST["article"]){
$_GET["article"] = $_REQUEST["article"];
	
	if ($_GET["article"]){
		$getProduct = mysql_query("
		SELECT name FROM s_articles WHERE id={$_GET["article"]}
		");
		if (!@mysql_num_rows($getProduct)){
			$title = $sLang["articles"]["skeleton_new_article"];
		}else {
			$title = $sLang["articles"]["skeleton_article"]." ".mysql_result($getProduct,0,"name")." ".$sLang["articles"]["skeleton_edit"];
		}
	} else {
		$title = $sLang["articles"]["skeleton_new_article"];
	}
	$title = htmlentities($title, ENT_COMPAT, 'ISO-8859-1', false);
	
}else {
		$title = $sLang["articles"]["skeleton_new_article"];
}
$port = (empty($_SERVER["HTTPS"]) ? "http" : "https");
?>
{
	"init": {
		"title": "<?php echo $title ?>",
		"minwidth": "876",
		"minheight": "450",
		"content": "",
		"loader": "none",
		"url": "",
		"help":"http://www.hamann-media.de/dev/wiki/Hilfe:Artikel#Neu"
	},
	"tabs": 
	[
		{
			"id": "step1",
			"title": "<?php echo $sLang["articles"]["skeleton_data"] ?>",
			"active": 1,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/artikeln1.inc.php?article=<?php echo $_GET["article"]?>'>",
			"show":"save",
			"help":"26"
		}
		,
		{
			"id": "step2",
			"title": "<?php echo $sLang["articles"]["skeleton_Categories"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/artikeln2.inc.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"41",
			"hideButtons": "true"
		}
		,
		{
			"id": "step3",
			"title": "<?php echo $sLang["articles"]["skeleton_pictures"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/artikeln3.inc.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"42",
			"hideButtons": "true"
		}
		,
		{
			"id": "step4",
			"title": "Eigenschaften",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/filter.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"42",
			"hideButtons": "true"
		}
		,
		{
			"id": "step5",
			"title": "<?php echo $sLang["articles"]["skeleton_Variants"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/varianten.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"43",
			"hideButtons": "true"
		},
		{
			"id": "step6",
			"title": "<?php echo $sLang["articles"]["skeleton_Configurator"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/config.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"32",
			"hideButtons": "true"
		},
		{
			"id": "step7",
			"title": "<?php echo $sLang["articles"]["skeleton_links"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/links.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"45",
			"hideButtons": "true"
		}
		,
		{
			"id": "step8",
			"title": "<?php echo $sLang["articles"]["skeleton_downloads"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/downloads.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"46",
			"hideButtons": "true"
		}
		,
		{
			"id": "step9",
			"title": "<?php echo $sLang["articles"]["skeleton_Cross-Selling"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/cross.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"47",
			"hideButtons": "true"
		}

		,
		{
			"id": "step10",
			"title": "<?php echo $sLang["articles"]["skeleton_esd"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/esd.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"140",
			"hideButtons": "true"
		}
		,
		{
			"id": "step11",
			"title": "Bundles",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/bundles.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"140",
			"hideButtons": "true"
		}
		,
		{
			"id": "step11",
			"title": "Live-Shopping",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/liveshopping/main.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"help":"140",
			"hideButtons": "true"
		}

		,
		{
			"id": "step12",
			"title": "<?php echo $sLang["articles"]["skeleton_Statistics"] ?>",
			"active": <?php echo $_GET["article"] ? "1" : "0" ?>,
			"content": "<div id='contentFrame' class='contentFrame' src='<?php echo $port ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/modules/articles/statistics.php?article=<?php echo $_GET["article"]?>'>",
			"hide":"save",
			"hideButtons": "true"
		}
		
	],
	"buttons": 
	[
		{
			"id": "save",
			"bind": "step1",
			"title": "<?php echo $sLang["articles"]["skeleton_Save_Changes"] ?>",
			"active": 1,
			"remotecall": "saveArticle",
			"remoteattribute": ""
		}
	]
	
}
