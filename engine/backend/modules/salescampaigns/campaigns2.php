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
<title><?php echo $sLang["salescampaigns"]["campaigns2_Reorder_TreePanel"] ?></title>
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
<style>
col1 {
	float: left;
	overflow: auto;
	width: 220px;
	margin: 0 0 0 0;
	padding: 5px 0 5px 5px;
	height: 98%;
	background-color: #fff;
	border-right: 1px solid #6b6b6b;
}

div.col1_pictures {
	float: left;
	overflow: auto;
	width: 250px;
	margin: 0 0 0 0;
	padding: 20px 0 5px 20px;
	height: 98%;
}
div.col1_top {
	background-color:#33CC66;
	color:#66CC00;
}
div.col2 {
	float: left;
	width: 730px;
	padding: 0;
	margin: 0;
	height: 98%;
}

</style>
<style>
.x-panel-body {
border-width:0;
}
.x-panel-body-noheader, .x-panel-mc .x-panel-body {
border-width:0;
}


</style>
<?php
if (!$_GET["categoryID"]){
?>
<script type="text/javascript" src="treeCategory.js"></script>
<?php
} else { 
?>
<script type="text/javascript" src="treeCampaign.php?categoryID=<?php echo $_GET["categoryID"]?>"></script>
<?php
}
?>
</head>

<body id="case1">
<div class="container">

<?php
if ($_GET["categoryID"]){
?>
	<div id="tree-div" class="col1" title="<?php echo $sLang["salescampaigns"]["campaigns2_move"] ?><br /> <?php echo $sLang["salescampaigns"]["campaigns2_drag_n_drop"] ?>">
	<input id="helpButton" type="submit" value="Aktualisieren" style="width:100px;height:22px;float:right">
	<input id="helpButton1" onclick="document.location.href='campaigns.php';" type="submit" value="Zurück" style="clear:left;float:left;width:100px;height:22px"><br /><br />
	</div>
<?php
} else {
?>
	<div id="tree-div" class="col1"></div>
<?php
}
?>
<?php
if ($_GET["categoryID"]){
?>
	<div class="col2" style="width:60%">
	
		<iframe id="articleList" src="campaignsedit.php?category=<?php echo $_GET["categoryID"]?>" border="0" frameborder="0" width="100%" style="width:100%" height="100%" scrolling="overflow"></iframe>
	</div>
<?php
} else {
?>
	<div class="col2" style="width:60%">
		<iframe id="articleList" src="start.php" border="0" frameborder="0" width="100%" style="width:100%"  height="90%" scrolling="overflow"></iframe>
	</div>
<?php
}
?>

<div class="clear"></div>
</div>




</body>
</html>
