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

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sLang["orders"]["main_search"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>

<script type="text/javascript" src="../../../backend/js/mootools.js"></script>

<link href="js/calendar.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />


<script type="text/javascript">
var myExt = function(){
	
	var iFrameId = Ext.id();
	
	//Blank Image

	//Def. Variables
	var root, tree, myTab;
	var Tree = Ext.tree;
	
	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		onRender : function(ct, position){
		  this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		}
	}); 

	return 	{
			openIFrameElement : function(page, openExtWin){
				if(page){
					var url=page+"?id=<?php echo $_GET["id"] ?>";
					Ext.get('framepanel'+iFrameId).dom.src = url;
				}
				if(openExtWin)
				{
					fenster = window.open (
						openExtWin,
						"NeuesFenster", // Name des neuen Fensters
						+"toolbar=0" // Toolbar
						+",location=1" // Adress-Leiste
						+",directories=0" // Zusatzleisten
						+",status=0" // Statusleiste
						+",menubar=0" // Menü
						+",scrollbars=1" // Scrollbars
						+",resizable=1" // Fenstergrösse veränderbar?
						+",width=800" // Fensterbreite in Pixeln
						+",height=600" // Fensterhöhe in Pixeln
					);
				}
					
			},
			init : function(){	
					//DEF TreePanel											
					var tree = new Tree.TreePanel({
						id:'feed-tree',
						region: 'west',
						autoScroll: true, 
						split: true,
						rootVisible: false,
						animate: false,
						collapsible: true,
						lines:false,
						title: 'Navigation',
						width: 120,
						listeners:{'click': function(e){
							if(e.attributes.template || e.attributes.openExtWin){
								myExt.openIFrameElement(e.attributes.template, e.attributes.openExtWin);
							}
						}}
					});
					
					//set root node
					root = new Tree.TreeNode({
						text: 'rootNode',
						id: '1'
					});
					
					tree.setRootNode(root);
					
					var nodes = ['Stammdaten', 'Positionen', 'Belege'];
					var tpl = ['main.php', 'positions.php', 'documents.php'];
					
					for(var i=0; i<nodes.length; i++)
					{
						root.appendChild(
							new Ext.tree.TreeNode({
								id: i+2,
								text: nodes[i],
								template: tpl[i]
							})
						);
					}				
					
					//Create IFrame for Viewport->CENTER
					var iframe = new Ext.ux.IFrameComponent({
						region:'center',
						split:true,
						animate:true,
						title: 'Test',
//							width:700,
//							height:500,
						collapsible:true,
						id: iFrameId,
						url: '',
						listeners: {'render': function(){
							myExt.openIFrameElement(tpl[0]);
						}}
					});
					
					//Set Viewport Options					
					var viewport = new Ext.Viewport({
						layout: 'border',
						items:[
							tree,
							iframe
						]
					});
				}
			}
}();

Ext.onReady(function(){	
	myExt.init();
});
</script>

</head>
<body>
</body>
</html>