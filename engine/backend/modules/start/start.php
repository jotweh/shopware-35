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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="de" xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<title></title>
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../../vendor/flashgraph/JSClass/FusionCharts.js"></script>


<style type="text/css">
a {color: #3e677d;}
a:hover {color: #3e677d !important;}
.box_left {
	width:49%; border-right: 1px solid #ccc; float:left;height:230px;
}
.box_right {
	width:49%; float:left; padding-left:10px;height:230px;
}
.box_white {
	min-width: 700px; padding: 10px; border: 1px solid #a9a9a9; border-top: none; background-color: #fff;  margin:0 5px;
}

strong, h2 {font-size:11px; font-weight: bold; color: #888;}
a:hover {background-color:#cee5f1 !important; color:#777 !important;}
table tr.inline:hover {background-color:#cee5f1;color:#3e677d !important;}
table tr.inline:hover a {color:#3e677d !important;}

div.table_zebra a:hover {color:#3e677d !important;}
</style>

</head>
<body>

<?php
//echo "<pre>";
//print_r($sCore);
//die();

//=====================================================================================================
// Chechlist items available?
//=====================================================================================================

$getModulesQ = mysql_query("
	SELECT `module` , `hash`
	FROM `s_core_licences`
");

$activeModsArray = array();
while ($module = mysql_fetch_array($getModulesQ)) {
	if ($sCore->sCheckLicense("","",$sCore->sLicenseData[$module['module']]))
		$activeModsArray[] = "'".$module['module']."'";
}
$activeMods = implode(", ", $activeModsArray);
$ANDmodules = "AND (  `module` = '' OR `module` IN ({$activeMods})   )";

//=====================================================================================================
// Get active paymentmeans
//=====================================================================================================

$getPaymentMeansQ = mysql_query("
	SELECT `name`
	FROM `s_core_paymentmeans`
	WHERE `active` = 1
");

$activePaymentMeansArray = array();
while ($paymentmean = mysql_fetch_array($getPaymentMeansQ)) {
		$activePaymentMeansArray[] = "'".$paymentmean['name']."'";
}
$activePaymentMeans = implode(", ", $activePaymentMeansArray);
$ANDpaymentmean = "AND (  `paymentmean` = '' OR `paymentmean` IN ({$activePaymentMeans})   )";
//=====================================================================================================


$getChecklistTotalQ = mysql_query("
	SELECT COUNT(*) AS total
	FROM `s_core_checklist`
	WHERE `checked` = 0
	{$ANDmodules}
	{$ANDpaymentmean}
");
$total = mysql_result($getChecklistTotalQ, 0, 'total');
$adjustData = empty($total) ? false : true;

//=====================================================================================================

//Default setting
$show_tabpanel = false;

$sHOST = $_SERVER['HTTP_HOST'];
if (
		preg_match('/shop-ftp\.de/i', $sHOST) ||
		preg_match('/shopftp\.de/i', $sHOST) ||
		preg_match('/shopftp2\.de/i', $sHOST) ||
		preg_match('/shopwaredemo\.de/i', $sHOST) ||
		preg_match('/dev\.shopvm\.de/i', $sHOST)
	) {
   	$show_tabpanel = true;
   	$demomodus = true;
	$tab_items = "startseite,lizenzmanager";
} else {
	$tab_items = "startseite";
   	$demomodus = false;
}

//Shop reset-function // HOST LIKE "shopwaredemo.de"
if (
		preg_match('/shop-ftp\.de/i', $sHOST) ||
		preg_match('/shopftp\.de/i', $sHOST) ||
		preg_match('/shopftp2\.de/i', $sHOST) ||
		preg_match('/shopwaredemo\.de/i', $sHOST) ||
		preg_match('/dev\.shopvm\.de/i', $sHOST)
	){ 
	$tab_items.= ",resetshop";
}

if($adjustData != false){
	$show_tabpanel = true;
	//$tab_items.= ",checklist";
}
?>

<script type="text/javascript">

Ext.onReady(function(){
		
	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	 onRender : function(ct, position){
	      this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
	 }
	});

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var startseite_sngle = new Ext.ux.IFrameComponent({ 
				title:'Startseite',
				region:'center',
				id: "idStartseite"+Ext.id(), 
				url: 'startpage.php'
			});

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var startseite = new Ext.ux.IFrameComponent({ 
				title:'Startseite',
				id: "idStartseite"+Ext.id(), 
				url: 'startpage.php?margintop=1'
			});

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var lizenzmanager = new Ext.ux.IFrameComponent({ 
				title:'Lizenzen aktivieren / deaktivieren',
				id: "idStartseite"+Ext.id(), 
				url: 'license.php'
			});
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	/*var checklist = new Ext.ux.IFrameComponent({ 
				title:'<b><span style="color:red;">Checkliste</span></b>',
				id: "idCheckliste"+Ext.id(), 
				url: 'checklist.php'
			});
	*/
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var resetshop = new Ext.ux.IFrameComponent({ 
				title:'Shop zurücksetzen',
				id: "idResetShop"+Ext.id(), 
				url: 'resetshop.php'
			});
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var tabs = new Ext.TabPanel({
	    activeTab: 0,
	    region:'center',
	    items: [<?php echo$tab_items;?>]
	});	
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	<?php if($show_tabpanel) { ?>
	
		new Ext.Viewport({
			layout: 'border',
			items: [tabs]
		});
		
	<?php }else{ ?>
	
		new Ext.Viewport({
			layout: 'border',
			items: [startseite_sngle]
		});
		
	<?php } ?>
});
</script>

<!-- END WHITE AREA -->

</body>
</html>
