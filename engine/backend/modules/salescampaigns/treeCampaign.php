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
if ($_GET["categoryID"] && $_GET["categoryID"]!=1){
	$getCategoryName = mysql_query("
	SELECT description FROM s_categories WHERE id={$_GET["categoryID"]}
	");
	$categoryName = @mysql_result($getCategoryName,0,"description");
	if (!$categoryName) die($sLang["salescampaigns"]["treecampaign_category_not_found"]);
}elseif($_GET["categoryID"]==1){
	$categoryName = "Start";
}
?>
/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */
 var iTree = function(){
    var root, tree;
    return {
        init : function(){
            // yui-ext tree
           var Tree = Ext.tree;
		    
           var tree = new Tree.TreePanel({
           		el: 'tree-div',
		        animate:false, 
		        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCampaigns.php'}),
		        enableDD:true,
		        enableEdit: true,
		        containerScroll: true
		    });
		    
		 

            
            tree.on('click', function(e){
            	///console.log(e.attributes);
            	switch (e.attributes.type){
            		case "CAMPAIGNS":
            			var url = "campaignsedit.php?id="+e.attributes.id;
            			break;
            		case "CAMPAIGN":
            			var url = "campaignsedit.php?id="+e.attributes.id+"&category=<?php echo $_GET["categoryID"]?>";
            			break;
            		case "BANNER":
            			var url = "banneredit.php?id="+e.attributes.id;
            			break;
            		case "LINKS":
            			var url = "linksedit.php?id="+e.attributes.id;
            			break;
            		case "LINK":
            			var url = "linkdetails.php?id="+e.attributes.id;
            			break;
            		case "TEXT":
            			var url = "textedit.php?id="+e.attributes.id;
            			break;
            		case "ARTICLES":
            			var url = "articlesedit.php?id="+e.attributes.id;
            			break;
            		case "ARTICLE":
            			var url = "articledetails.php?id="+e.attributes.id;
            			break;
            		default:
            			var url = "campaignsedit.php?category=<?php echo $_GET["categoryID"]?>";
            		break;
            	}
            	
		        
		        document.getElementById('articleList').src = url; 
				
				
    		});  
    		tree.on('nodedrop',function(e){
    			
    			//return false;
    			var affectedNodes = tree.getNodeById(e.target.attributes.parentId);
    			var group = e.target.attributes.parentId;
    			var nodeData =affectedNodes.childNodes;
    			//console.log(nodeData);
    			var nodeTest = new Array();
    			
    			
    			
    			//nodeTest[2] = 3;
		        for(var i = 0, len = nodeData.length; i < len; i++){
		        	 var data = nodeData[i];
		        	 var nodeId = data.attributes.dbId;
		        	 //console.log(nodeId);
		        	 var nodePosition = i+1;
		        	 
		        	 nodeTest[i] = new Object;
    				 nodeTest[i].position = nodePosition;
    				 nodeTest[i].id = nodeId;
    			      	 
		        }
		        
		      
		        
		        conn = new Ext.data.Connection();
					conn.request({
					url: '../../../backend/ajax/reposCampaign.php',
					method: "POST",
					params: {
							nodes: Ext.encode(nodeTest),
							group: group
					},
					callback: function(o, success, response) {
						if ( !success ) {
							parent.Growl('<?php echo $sLang["salescampaigns"]["treecampaign_serverconnection_failed"] ?>');
							return false;
						}else {
							parent.Growl('<?php echo $sLang["salescampaigns"]["treecampaign_element_moved"] ?>');
							return false;
						}
								
					}
					});
    		});
    		
    		Ext.get('helpButton').on('click', function() {
				tree.root.reload();
			});
	
    		tree.on('nodedragover',function(e){
    			//console.log(e.target.attributes);
    			// Einfügen in andere Kategorie verhindern
    			if (e.point=="append") return false;
    			// Verschieben nur bei gleichem Parent-Element
    			if (e.target.attributes.parentId != e.data.node.attributes.parentId) return false;
    			
    			
    			
    			return true;
    			/*if (e.target.firstChild.id){
    				//console.log("Failure");
    				//console.log(e);
    				return false;
    			}else {
    			//	console.log("Success");
    				//console.log(e);
    				return true;
    			}*/
    			/*if (e.dropNode.attributes.parentId==e.target.attributes.id){
    				return true;
    			}else {
    				console.log(e);
    				return true;
    			}*/
    			//console.log(e);
    			
    			
    		});
            tree.on('beforeclick', function(node){
                if(this.getSelectionModel().isSelected(node)){
                	
                    ge.node = node;
                    
                    ge.startEdit(node.ui.getAnchor(), node.text);
                    return false;
                }
                
    		});
            
             
            // set the root node
		    var root = new Tree.AsyncTreeNode({
		        text: '<?php echo $categoryName ?>',
		        draggable:false,
		        id:'CAMPAIGNS:<?php echo $_GET["categoryID"] ?>'
		    });
            tree.setRootNode(root);
			
            // render the tree
            tree.render();
			
           
            root.expand();        
       	 	}
    	};
}();

Ext.onReady(function(){    
   // if (!document.getElementById('tree-div')){
    	iTree.init();
   // }else {
    	//iTree2.init();
   // }
    
});

