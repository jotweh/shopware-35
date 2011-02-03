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

<script type="text/javascript">

Ext.onReady(function(){
		
	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	 onRender : function(ct, position){
	      this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
	 }
	});
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var systeminfo = new Ext.ux.IFrameComponent({ 
				title:'Systeminfo',
				id: "idSysteminfo"+Ext.id(), 
				url: 'sSystemCheck.php'
			});

	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var phpinfo = new Ext.ux.IFrameComponent({ 
				title:'PhpInfo',
				id: "idphpinfo"+Ext.id(), 
				url: 'phpinfo.php'
			});
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	var tabs = new Ext.TabPanel({
	    activeTab: 0,
	    region:'center',
	    items: [systeminfo, phpinfo]
	});	
	
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	new Ext.Viewport({
		layout: 'border',
		items: [tabs]
	});
});
</script>

<!-- END WHITE AREA -->

</body>
</html>
