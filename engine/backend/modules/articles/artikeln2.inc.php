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
if (!$_GET["article"]){
	die("No article referenced");
}
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
  
	/*
	Save Treepanels state
	*/
	TreePanelState = function(mytree, attributeName) {
	  this.mytree = mytree;
	  this.myattr = attributeName || 'id';
	}
	
	TreePanelState.prototype.init = function() {
	  this.cp = new Ext.state.CookieProvider();
	  //this.cp.set('TreePanelState_' + this.mytree.id, new Array());
	  this.state = this.cp.get('TreePanelState_' + this.mytree.id, new Array());
	}
	
	TreePanelState.prototype.saveState = function(newState) {
	  this.state = newState;
	  this.cp.set('TreePanelState_' + this.mytree.id, this.state);
	}
	
	TreePanelState.prototype.onExpand = function(node) {
	  var currentPath = node.getPath(this.myattr);
	  var newState = new Array();
	  for (var i = 0; i < this.state.length; ++i) {
	    var path = this.state[i];
	    if (currentPath.indexOf(path) == -1) {
	      // this path does not already exist
	      newState.push(path);			
	    }
	  }
	  // now ad the new path
	  newState.push(currentPath);
	  this.saveState(newState);
	}
	
	TreePanelState.prototype.onCollapse = function(node){
	  var closedPath = closedPath = node.getPath(this.myattr);
	  var newState = new Array();
	  for (var i = 0; i < this.state.length; ++i) {
	    var path = this.state[i];
	    if (path.indexOf(closedPath) == -1) {
	      // this path is not a subpath of the closed path
	      newState.push(path);			
	    }
	  }
	  if (newState.length == 0) {
	    var parentNode = node.parentNode;
	    newState.push((parentNode == null ? this.mytree.pathSeparator : parentNode.getPath(this.myattr)));
	  }
	  this.saveState(newState);
	}
	
	TreePanelState.prototype.restoreState = function(defaultPath) {	
		
		if (this.state.length == 0) {
			var newState = new Array(defaultPath);
			this.saveState(newState);		
			this.mytree.expandPath(defaultPath, this.myattr);
			return;
		}
		
		var stateToRestore=this.state;
		//console.log(stateToRestore);
		for (var i = 0; i < stateToRestore.length; ++i) {
			// activate all path strings from the state
			try {
	      var path = this.state[i];
	      this.mytree.expandPath(path, this.myattr);  		
			} catch(e) {
				// ignore invalid path, seems to be remove in the datamodel
				// TODO fix state at this point
			}
		}	
	}
	
	
	
	tree = new Tree.TreePanel({
                	region:'west',
                	split:true,
                	fitToFrame: true,
			        animate:false, 
			        collapsible: true,
			        title:'Kategorien',
			        width: 250,
			        height:'100%',
			        margins:'0 0 0 0',
			        minSize: 175,
			        id: 'cattree',
			        <?php
			        if (empty($_REQUEST["blog"])){
			        ?>
			        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCategories.php'}),
			        <?php
			        }else {
			        ?>
			         loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getBlogCategories.php'}),
			        <?php
			        }
			        ?>
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
		title:'Einstellungen',
		width:700,
        height:500,
		collapsible: true,
		id: "myiframe", 
		url: 'categoryrelations.php?article=<?php echo $_GET["article"]?>' 
	});
	
	tree.on('click', function(e){
		if (e.attributes.leaf){
		var text = e.attributes.text;
		text = text.replace(/\#/,"");
		text = text.replace(/\#/,"");
        var url = "categoryrelations.php?catid="+e.attributes.id+"&text="+text+"&article=<?php echo $_GET["article"] ?>"; 
	
		 Ext.get('framepanelmyiframe').dom.src = url;
		 parentNode = e.parentNode;
		 currentNode = e;
		}
    });  
    
	
    		
	tree.setRootNode(root);
	root.expand();
	/*
	Save Treepanels state
	*/
	
	var gtState = new TreePanelState(tree, 'id');
	gtState.init();
	tree.on('collapsenode', gtState.onCollapse, gtState);
	tree.on('expandnode', gtState.onExpand, gtState);
	tree.getRootNode().reload(function(node ) {
		gtState.restoreState(node.getOwnerTree().getRootNode().getPath('id'));
		node.getOwnerTree().on('collapsenode', gtState.onCollapse, gtState);
		node.getOwnerTree().on('expandnode', gtState.onExpand, gtState);
		//if (selectedPath)
		//node.getOwnerTree().selectPath(selectedPath, 'cattree');
		
	});
	
	
   
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

