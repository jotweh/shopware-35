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
  <title><?php echo $sLang["presettings"]["settings_articles_overview"] ?></title>
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
    Ext.onReady(function(){

 
    	
    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
     }
	}); 
	
    var Tree = Ext.tree;
       
       
	var tree = new Tree.TreePanel({
                	region:'west',
                	split:true,
                	fitToFrame: true,
			        animate:true, 
			        collapsible: true,
			        title:'<?php echo $sLang["presettings"]["settings_settings"] ?>',
			        width: 200,
			        height:'100%',
			        margins:'0 0 0 0',
			        minSize: 175,
			        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getSettings.php'}),
			        enableDD:false,
			        enableEdit:false,
			        autoScroll: true,
			        rootVisible:false
	});
	var root = new Tree.AsyncTreeNode({
		        text: '<?php echo $sLang["presettings"]["settings_shopware_configuration"] ?>',
		        draggable:true,
		        id:'0',
		        direct:''
	});
	
	var iframe = new Ext.ux.IFrameComponent({ 
		region:'center',
		split:true,
		animate:false, 
		fitToFrame: true,
		title:'<?php echo $sLang["presettings"]["settings_settings"] ?>',
		width:700,
        height:500,
		collapsible: true,
		id: "myiframe", 
		url: 'help.php' 
	});
	
	tree.on('click', function(e){
		if (e.attributes.id){
		    
	    	if (!e.attributes.file && !e.attributes.action){
	    		var url = "settingsdetail.php?id="+e.attributes.id;
	        }
	        else if (e.attributes.action){
	        	var url = "../../../../"+e.attributes.action;
	        }
	        else {
	        	var url = e.attributes.file;
	        }
	       // console.log(iframe);
	        Ext.get('framepanelmyiframe').dom.src = url;

		}		
		
    });  
    		
	   tree.setRootNode(root);
	   root.expand();
	  
	   var header = new Ext.BoxComponent({ // raw
                    region:'north',
                    el: 'myInfo',
                    height:32
    });
    
       var viewport = new Ext.Viewport({
            layout:'border',
            items:[
					iframe,
					tree
             ]
        });
       var myDiv = Ext.get('myInfo');
       
           
       Ext.MessageBox.show({
		title: '<?php echo $sLang["presettings"]["settings_important"] ?>',
		msg: 'Bitte beachten Sie die Hinweise in unserem Wiki!',
		width:400,
		buttons: Ext.MessageBox.OK,
		animEl: myDiv
	});  
         
       
    });
	</script>
</head>
<body>




  

<div id="myInfo">
</div>
 

 </body>
</html>

