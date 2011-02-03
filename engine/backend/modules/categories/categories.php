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
<html xmlns="http://www.w3.org/1999/xhtml" lang="de" xml:lang="de">
<head>
  <meta http-equiv="Content-Language" content="de" />
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title></title>
	<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
	<link href="../../../backend/css/icons4.css" rel="stylesheet" type="text/css" />
	<link href="../../../vendor/ext/resources/css/ext-all.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
	<script type="text/javascript" src="../../../vendor/ext/build/locale/ext-lang-de.js" charset="utf-8"></script>
 	<script type="text/javascript" src="../../../backend/js/mootools.js"></script>
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
    a.ico {
		float:left;
		height:20px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
	}
    </style>
	
	<script type="text/javascript">
	var root, tree, currentNode, parentNode, iframe;
	
	var myExt = {
	    reload : function(){
	    	try {
	    		currentNode.reload();
	    	} catch (e){
	    		this.reloadParent();
	    	}
	    },
	    reloadParent : function(){
	    	parentNode.reload();
	    }
    };
    
Ext.onReady(function(){

    Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
     onRender : function(ct, position){
          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
     }
	}); 
  
	tree = new Ext.tree.TreePanel({
		region:'west',
		split:true,
		fitToFrame: true,
		animate:false,
		title:'<?php echo $sLang["categories"]["categories_Categories"] ?>',
		width: 200,
		height:'100%',
		margins:'0 0 0 0',
		minSize: 175,
		loader: new Ext.tree.TreeLoader({dataUrl:'ajax/getCategories.php?move=1'}),
		enableDD:true,
		enableEdit:true,
		autoScroll: true,
		root: new Ext.tree.AsyncTreeNode({
			 text: '<?php echo $sLang["categories"]["categories_shopware"] ?>',
			 draggable:true,
			 id:'1'
		})
	});
	
	tree.on('click', function(e){
		 var url = "categoryedit.php?id="+e.attributes.id;
		 iframe.el.dom.src = url;
		 if(!e.attributes.id!=1)
		 {
		 	iframe2.enable();
		 	iframe2.el.dom.src = url+'&settings=1';
		 }
		 else
		 {
		 	iframe2.disable();
		 }
		 
		 if(!e.attributes.child)
		 {
		 	Ext.getCmp('articles').disable();
		 	Ext.getCmp('tabs').activate(0);
		 }
		 else
		 {
			 articlestore.baseParams.categoryID = e.attributes.id;
			 articlestore.load();
	         articlestore2.baseParams.categoryID = e.attributes.id;
	         articlestore2.load();
	         Ext.getCmp('articles').enable();
		 }
		 
		 parentNode = e.parentNode;
		 currentNode = e;
    });  

	var iframe = new Ext.ux.IFrameComponent({ 
		region:'center',
		split:true,
		animate:true, 
		fitToFrame: true,
		title:'Übersicht',
		width:700,
        height:500,
		collapsible: true,
		id: "iframe", 
		url: '' 
	});
	
	var iframe2 = new Ext.ux.IFrameComponent({ 
		region:'center',
		disabled : true,
		split:true,
		animate:true, 
		fitToFrame: true,
		title:'Einstellungen',
		width:700,
        height:500,
		collapsible: true,
		id: "iframe2", 
		url: '' 
	});
	
    Ext.tree.TreeEditor.override({
	    triggerEdit : function(node, defer){
	        this.completeEdit();
	        if(node.attributes.editable !== false){
	            this.editNode = node;
	            this.autoEditTimer = this.startEdit.defer(this.editDelay, this, [node.ui.textNode, node.text]);
	            return false;
	        }
	    }
	});
	
    var ge = new Ext.tree.TreeEditor(tree, null);
    
	Ext.apply(this.ge, {
		ignoreNoChange: true,
		completeOnEnter: true,
		allowBlank:false,
		blankText:'<?php echo $sLang["categories"]["categories_a_name_is_reqiored"] ?>'
	});

	ge.on('complete',function (element,newValue,oldValue){
 		conn = new Ext.data.Connection();
		conn.request({
		url: '../../../backend/ajax/renameCategory.php',
		method: "POST",
		params: {
				id: element.editNode.id, 
				oldValue: oldValue,
				newValue: newValue
		},
		callback: function(o, success, response) {
			if ( !success ) {
				parent.Growl('<?php echo $sLang["categories"]["categories_connection_to_server_failed"] ?>');
				return false;
			}else {
				parent.Growl('<?php echo $sLang["categories"]["categories_Category_has_been_renamed"] ?>');
				return false;
			}
					
		}
		});
    });
    
    tree.on('nodedrop',function(e){
		var nodeData = e.data.node.parentNode.childNodes;
		var nodes = new Array();
		var nodeID = 0;
		for(var i = 0, len = nodeData.length; i < len; i++)
		{
        	 nodes[nodeID] = new Object;
			 nodes[nodeID].position = i+1;
			 nodes[nodeID].id = nodeData[i].id;
			 if(nodeData[i].id == e.data.node.id && e.data.node.attributes.parentId != e.data.node.parentNode.id)
			 {
			 	nodes[nodeID].parentID = e.data.node.parentNode.id;
			 	nodes[nodeID].oldParentID = e.data.node.attributes.parentId;
			 }
			 nodeID++;
        }
        if(e.data.node.attributes.parentId != e.data.node.parentNode.id)
        {
	        var nodeData = tree.getNodeById(e.data.node.attributes.parentId).childNodes;
			e.data.node.attributes.parentId = e.data.node.parentNode.id;
			
			for(var i = 0, len = nodeData.length; i < len; i++)
			{
	        	 nodes[nodeID] = new Object;
				 nodes[nodeID].position = i+1;
				 nodes[nodeID].id = nodeData[i].id;
				 nodeID++;
	        }
        }
        
		Ext.MessageBox.confirm('Confirm', 'Wollen Sie diese Kategorie wirklich verschieben?', function(r){
    		if(r=='yes')
    		{
    			conn = new Ext.data.Connection();
				conn.request({
					url: '../../../backend/ajax/reposCategories.php',
					method: "POST",
					params: {
							nodes: Ext.encode(nodes)
					},
					callback: function(o, success, response) {
						if ( !success ) {
							parent.Growl('<?php echo $sLang["categories"]["categories_connection_to_server_failed"] ?>');
							return false;
						}else {
							parent.Growl('<?php echo $sLang["categories"]["categories_Category_has_been_moved"] ?>');
							return false;
						}
					}
				});
    		}
    		else
    		{
    			tree.getRootNode().reload();
    		}
        });
    });
    	
    tree.on('nodedragover',function(e){
    	return true;	
    }); 
 
	tree.getRootNode().expand();
	
	<?php echo file_get_contents(dirname(__FILE__).'/js/articles.js');?>
	    
    var tabs = new Ext.TabPanel({
    	id: 'tabs',
    	region:'center',
        enableTabScroll:true,
        deferredRender:false,
        activeTab:0,
        defaults: {autoScroll:true},
        items: [iframe, iframe2, articles]
    });
   
	var viewport = new Ext.Viewport({
	    layout:'border',
	    items:[
			tabs,
			tree
	     ]
	});
	
	
    var firstGridDropTargetEl =  articlegrid.getView().scroller.dom;
	var firstGridDropTarget = new Ext.dd.DropTarget(firstGridDropTargetEl, {
		ddGroup    : 'firstGridDDGroup',
		notifyDrop : function(ddSource, e, data){
			var records =  ddSource.dragData.selections;
			Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
			articlegrid.store.add(records);
			//articlegrid.store.sort('customernumber', 'ASC');
			return true
		}
	});
	
	var secondGridDropTargetEl = articlegrid2.getView().scroller.dom;
	var secondGridDropTarget = new Ext.dd.DropTarget(secondGridDropTargetEl, {
		ddGroup    : 'secondGridDDGroup',
		notifyDrop : function(ddSource, e, data){
			var records =  ddSource.dragData.selections;
			Ext.each(records, ddSource.grid.store.remove, ddSource.grid.store);
			articlegrid2.store.add(records);
			//articlegrid2.store.sort('customernumber', 'ASC');
			return true
		}
	});
	
});
</script>
</head>
<body>
</body>
</html>