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

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo $sLang["salescampaigns"]["start_reorder_treePanel"] ?></title>
<!-- Common Styles for the examples -->
</head>

<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

<body>






<fieldset class="col2_cat2" style="width:400px;">
<legend><a class="ico help"></a><?php echo $sLang["salescampaigns"]["start_shopware_actions"] ?></legend>
<?php echo $sLang["salescampaigns"]["start_the_tool_to_easily_create_individual_deals"] ?><br />
<?php echo $sLang["salescampaigns"]["start_please_select_the_category"] ?>
<?php
	$queryParent = mysql_query("
	SELECT parentID FROM s_core_multilanguage WHERE
	isocode='de'
	");
	if (@mysql_num_rows($queryParent)){
		$start = mysql_result($queryParent,0,"parentID");
	}else {
		$start = 1;
	}
	
	
if ($_GET["id"] && $_GET["id"]!=$start){
	
	$getCategoryName = mysql_query("
	SELECT description FROM s_categories WHERE id={$_GET["id"]}
	");
	$categoryName = @mysql_result($getCategoryName,0,"description");
	if (!$categoryName) die($sLang["salescampaigns"]["start_category_not_found"]);
	?>
	<br />
	<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="parent.location.href='campaigns.php?categoryID=<?php echo $_GET["id"] ?>';" type="submit" value="send" class="button"><div class="buttonLabel">Kategorie <?php echo $categoryName ?> <?php echo $sLang["salescampaigns"]["start_edit"] ?></div></button></li>	
		</ul>
	</div>
	<?php
}else {
	if (!$_GET["id"]) $_GET["id"] = $start;
?>
<br />

		<div class="buttons" id="buttons">
		<ul>
		<li id="buttonTemplate" class="buttonTemplate"><button onClick="parent.location.href='campaigns.php?categoryID=<?php echo $_GET["id"] ?>';" type="submit" value="send" class="button"><div class="buttonLabel">Startseiten-Aktionen <?php echo $categoryName ?> <?php echo $sLang["salescampaigns"]["start_edit"] ?></div></button></li>	
		</ul>
		</div>
<?php
}
?>
</fieldset>



<div class="clear"></div>
</body>
</html>
