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
  <title>articles.overview</title>
 	<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
	<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>

	
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
 	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />

 	
	<style type="text/css">
	html, body {
        font:normal 12px verdana;
        margin:0;
        padding:0;
        border:0 none;
        height:100%;
    }
	p {
	    margin:5px;
	}
    .settings {
        background-image:url(../shared/icons/fam/folder_wrench.png);
    }
    .nav {
        background-image:url(../shared/icons/fam/folder_go.png);
    }
    </style>
	
	<script type="text/javascript">
   
	
	var myExt = function(){
    var root, tree, currentNode, parentNode;
    var Tree = Ext.tree;
    return {
    reload : function(){
    	try {
    		currentNode.reload();
    	} catch (e){
    		this.reloadParent();
    	}
    },
    reloadParent : function(){
    	parentNode.reload();
    },
    init : function(){
    	
    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
     }
	}); 
  
	tree = new Tree.TreePanel({
                	region:'west',
                	split:true,
                	fitToFrame: true,
			        animate:false, 
			        collapsible: true,
			        title:'<?php echo $sLang["browser"]["browser_root"] ?>',
			        width: 200,
			        height:'100%',
			        margins:'0 0 0 0',
			        minSize: 175,
			        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getBrowser.php'}),
			        enableDD:true,
			        enableEdit:true,
			        autoScroll: true,
			        rootVisible:false
	});
	var root = new Tree.AsyncTreeNode({
		 text: 'Shopware',
		  draggable:true,
		        id:'0',
		        direct:''
	});
	
	var iframe = new Ext.ux.IFrameComponent({ 
		region:'center',
		split:true,
		animate:true, 
		fitToFrame: true,
		title:'<?php echo $sLang["browser"]["browser_options"] ?>',
		width:700,
        height:500,
		collapsible: true,
		id: "myiframe", 
		url: '' 
	});
	
	tree.on('click', function(e){
		 var url = "options.php?id="+e.attributes.id;
		 Ext.get('framepanelmyiframe').dom.src = url;
		 parentNode = e.parentNode;
		 currentNode = e;
    });  
    
	
    		
	tree.setRootNode(root);
	root.expand();
	
   
	var viewport = new Ext.Viewport({
	    layout:'border',
	    items:[
				iframe,
				tree
	     ]
	});
       
       
         
}};
}();
Ext.onReady(function(){
	myExt.init();
});
	</script>
</head>
<body>




  


 </body>
</html>

