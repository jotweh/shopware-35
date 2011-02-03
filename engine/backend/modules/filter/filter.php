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
<script>
/*
 * Ext JS Library 2.2
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

var TreeTest = function(){
    // shorthand
    var Tree = Ext.tree;
    var tree,tree2;
    return {
    	reload : function (){
    		tree.root.reload();
    		tree2.root.reload();
    		tree.expandAll(); 
   		},
        init : function(){
        	Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		     onRender : function(ct, position){
		          this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		     }
			}); 
			
            // yui-ext tree
            tree = new Tree.TreePanel({
                region:'west',
                animate:false, 
                autoScroll:true,
                split:true,
                width: 280,
                maxSize: 280,
                useArrows: true,
                margins:'0 0 0 5',
                fitToFrame: true,
                title:'Gruppen',
                loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getFilterGroups.php'}),
                enableDD:true,
                containerScroll: true,
                collapseFirst:true,
				tbar: [{
				    text:'Neue Gruppe',
				    handler: function(){
				    	var url = "manage.php?groupNew=1";
						Ext.get('framepanelmyiframe').dom.src = url;
				    },
				    scope: this
				},{
				    id:'delete',
				    text:'Gruppe löschen',
				    disabled: true,
				    handler: function(){
				    	
				        var s = tree.getSelectionModel().getSelectedNode();
				        if(s && s.attributes.id!="0"){
				        	
				            removeGroup(s.attributes.id);
				        }
				    },
				    scope: this
				},{
				    id:'delete2',
				    text:'Zuordnung löschen',
				    disabled: true,
				    handler: function(){
				    	
				        var s = tree.getSelectionModel().getSelectedNode();
				        if(s && s.attributes.id!="0"){
				        	
				            removeGroupRelation(s.attributes.id);
				        }
				    },
				    scope: this
				}]
            });
            function removeGroup(id){
				Ext.MessageBox.confirm('Filter', 'Soll die markierte Gruppe wirklich gelöscht werden', 
					function deleteClientConfirmed(btn,id){
			    		if (btn=="yes"){
		    				var group = tree.getSelectionModel().getSelectedNode().attributes.id;
		    				var name = tree.getSelectionModel().getSelectedNode().attributes.name;
		    				if (group){
									Ext.Ajax.request({
				                    waitMsg: 'Lösche Gruppe',
				                    url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteFilterGroup.php', 
				                    params: { 
				                        group: group								
				                    },
				                    failure:function(response,options){
				                        Ext.MessageBox.alert('Fehler','Gruppe konnte nicht gelöscht werden');
				                    },                             
				                    success:function(response,options){
				                    	if (response.responseText){
				                    		 Ext.MessageBox.alert('Fehler',response.responseText);
				                    	}else {
											parent.Growl('Gruppe '+name+' wurde gelöscht');
											tree.root.reload();
											tree.expandAll(); 
				                    	}
				                    }                                   
				            	}); 
		    				}
				    	}
		    		}
    			)
            };
            
             function removeGroupRelation(id){
				Ext.MessageBox.confirm('Filter', 'Soll die markierte Verknüpfung wirklich gelöscht werden', 
					function deleteClientConfirmed(btn,id){
			    		if (btn=="yes"){
		    				var group = tree.getSelectionModel().getSelectedNode().attributes.id;
		    				var name = tree.getSelectionModel().getSelectedNode().attributes.name;
		    				if (group){
									Ext.Ajax.request({
				                    waitMsg: 'Lösche Verknüpfung',
				                    url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteFilterRelation.php', 
				                    params: { 
				                        group: group								
				                    },
				                    failure:function(response,options){
				                        Ext.MessageBox.alert('Fehler','Verknüpfung konnte nicht gelöscht werden');
				                    },                             
				                    success:function(response,options){
				                    	if (response.responseText){
				                    		 Ext.MessageBox.alert('Fehler',response.responseText);
				                    	}else {
											parent.Growl('Verknüpfung '+name+' wurde gelöscht');
											tree.root.reload();
											tree.expandAll(); 
				                    	}
				                    }                                   
				            	}); 
		    				}
				    	}
		    		}
    			)
            };
            // add a tree sorter in folder mode
            //new Tree.TreeSorter(tree, {folderSort:true});
            
            // set the root node
            var root = new Tree.AsyncTreeNode({
                text: 'Filter-Gruppen', 
                collapseFirst:true,
                selectable: false,
                draggable:false, // disable root node dragging
                id:'0'
            });
            tree.setRootNode(root);
            tree.expandAll(); 
            // render the tree
       
            
            root.expand(false, /*no anim*/ false);
            
            //-------------------------------------------------------------
            
            // YUI tree            
            tree2 = new Tree.TreePanel({
                region:'center',
                animate:false,
                autoScroll:true,
                split:true,
                width: 250,
                minSize: 250,
                maxSize: 250,
                useArrows: true,
                margins:'0 0 0 5',
                fitToFrame: false,
                title:'Optionen',
                //rootVisible: false,
                loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getFilterOptions.php'}),
                containerScroll: true,
                enableDD:true,
                rootvisible:false,
                dropConfig: {appendOnly:true},
				tbar: [{
				    text:'Neue Option',
				    handler: function(){
				    	var url = "manage.php?optionNew=1";
						Ext.get('framepanelmyiframe').dom.src = url;
				    },
				    scope: this
				},{
				    id:'deleteOption',
				    text:'Option löschen',
				    disabled: true,
				    handler: function(){
				    	
				        var s = tree2.getSelectionModel().getSelectedNode();
				        if(s && s.attributes.id!="0"){
				        	
				            removeOption(s.attributes.id);
				        }
				    },
				    scope: this
				}]
            });
            function removeOption(id){
				Ext.MessageBox.confirm('Filter', 'Soll die markierte Option wirklich gelöscht werden', 
					function deleteClientConfirmed(btn,id){
			    		if (btn=="yes"){
		    				var group = tree2.getSelectionModel().getSelectedNode().attributes.id;
		    				var name = tree2.getSelectionModel().getSelectedNode().attributes.name;
		    				if (group){
									Ext.Ajax.request({
				                    waitMsg: 'Lösche Option',
				                    url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteFilterOption.php', 
				                    params: { 
				                        group: group								
				                    },
				                    failure:function(response,options){
				                        Ext.MessageBox.alert('Fehler','Option konnte nicht gelöscht werden');
				                    },                             
				                    success:function(response,options){
				                    	if (response.responseText){
				                    		 Ext.MessageBox.alert('Fehler',response.responseText);
				                    	}else {
											parent.Growl('Option '+name+' wurde gelöscht');
											tree2.root.reload();
				                    	}
				                    }                                   
				            	}); 
		    				}
				    	}
		    		}
    			)
            };
            tree2.on('nodedragover', function(e){
            	return false;
            });
            
            tree.on('nodedragover', function(e){ 
            	if (e.dropNode.attributes.leaf==false) return false;
            	if (e.dropNode.attributes.id.match(/\_/)==null && e.point!="append"){
            		// New options are only allowed to insert into existing groups (Prevents moving of nodes) 
            		return false;
            	}
            	
            	if (e.dropNode.attributes.id.match(/\_/) &&  (e.target.attributes.parentId != e.data.node.attributes.parentId)){
 					// Allow move of nodes only in the filter-group
            		return false;
            	}
            	if (e.dropNode.attributes.id.match(/\_/) && e.point=="append"){
            		// Prevent moving of assigned childs 
            		return false;
            	}
            	
            	return true;
    			
            });
            
            tree.on('click', function(e){
         		if (e.attributes.leaf==false){
            		Ext.getCmp('delete').enable();
            		Ext.getCmp('delete2').disable();
         		}else {
         			if (e.attributes.id != "0")
         			{         		
         				Ext.getCmp('delete').disable();
         				Ext.getCmp('delete2').enable();
         			}else {
         				Ext.getCmp('delete').disable();
         				Ext.getCmp('delete2').disable();
         			}
         		}
            	// Display group/option details
            	if (e.attributes.leaf == false){
            		 var url = "manage.php?groupEdit="+e.attributes.id;
					 Ext.get('framepanelmyiframe').dom.src = url;
            	}
            });
            
            tree2.on('click', function(e){
         		
	 			if (e.attributes.id != "0")
	 			{         		
	 				Ext.getCmp('deleteOption').enable();
	 			}else {
	 				Ext.getCmp('deleteOption').disable();
	 			}
         		
            	// Display group/option details
            	if (e.attributes.id != "0"){
            		 var url = "manage.php?optionEdit="+e.attributes.id;
					 Ext.get('framepanelmyiframe').dom.src = url;
            	}
            });
            
            
            tree.on('beforenodedrop', function(e){
            	
            	
            	if (e.dropNode.attributes.id.match(/\_/) && e.point!="append"){
            		// Trigger moving of options within a group
            		
            		return true;
            	}
            	
            	// Make sure that node is unique (Unique assign of options into groups)
            	var parentId = e.target.attributes.id;
            	if (parentId=="0") return false;
            	if (e.dropNode.attributes.id.match(/\_/)) return false;
            	var dropId = parentId+"_"+e.dropNode.attributes.id;
            	var childs = e.target.childNodes;
            	
            	for(var i = 0, len = childs.length; i < len; i++){
            		if (childs[i].attributes.id==dropId){
            			return false;
            		}
            	}
			    
            	var n = e.dropNode; // the node that was dropped
			    var copy = new Ext.tree.TreeNode( // copy it
			          Ext.apply({}, n.attributes) 
			    );
			    
			    copy.attributes.id = dropId;
			    copy.attributes.parentId = parentId;
			    
			    // New option droped - refresh database
			    conn = new Ext.data.Connection();
				conn.request({
					url: '../../../backend/ajax/setFilterOption.php',
					method: "POST",
					params: {
							node: Ext.encode(copy.attributes)
					},
					callback: function(o, success, response) {
						if ( !success ) {
							parent.Growl('<?php echo "Es ist ein unbekannter Fehler aufgetreten" ?>');
							return false;
						}else {
							parent.Growl('<?php echo "Option wurde eingefügt" ?>');
							return false;
						}
								
					}
				});
			    
			    copy.id = dropId;
			    e.dropNode = copy; // assign the copy as the new dropNode
			});
			
			 tree.on('nodedrop',function(e){
			 	if (e.dropNode.attributes.id.match(/\_/) && e.point!="append"){
            		// Trigger moving of options within a group
            		
            		var affectedNodes = tree.getNodeById(e.target.attributes.parentId);
	    			var nodeData =affectedNodes.childNodes;
	    			var nodeTest = new Array();
					 
	    			for(var i = 0, len = nodeData.length; i < len; i++){
			        	 var nodePosition = i+1;
			        	 nodeTest[i] = new Object;
	    				 nodeTest[i].position = nodePosition;
	    				 nodeTest[i].id = nodeData[i].id;
			        	 nodeTest[i].name = nodeData[i].name;
	    			}
	    			
	    			conn = new Ext.data.Connection();
						conn.request({
							url: '../../../backend/ajax/setFilterOptionPosition.php',
							method: "POST",
							params: {
									node: Ext.encode(nodeTest)
							},
							callback: function(o, success, response) {
								if ( !success ) {
									parent.Growl('<?php echo "Es ist ein unbekannter Fehler aufgetreten" ?>');
									return false;
								}else {
									parent.Growl('<?php echo "Option wurde verschoben" ?>');
									return false;
								}
										
							}
					});
            		return true;
            	}
   			 });
			
			
            // add a tree sorter in folder mode
          //  new Tree.TreeSorter(tree2, {folderSort:true});
            
            // add the root node
            var root2 = new Tree.AsyncTreeNode({
                text: 'Filter-Optionen', 
                draggable:false, 
                id:'0'
            });
            tree2.setRootNode(root2);
           
            
            root2.expand(false, /*no anim*/ false);
            
			var iframe = new Ext.ux.IFrameComponent({ 
				region:'center',
				split:true,
				animate:true, 
				margins:'0 0 0 5',
				style: 'border: 1px solid',
				fitToFrame: false,
				title:'Test',
				collapsible: true,
				id: "myiframe", 
				url: 'manage.php' 
			});
	
        	var viewport = new Ext.Viewport({
			    layout:'border',
			    items:[
				   new Ext.Panel({
					layout: 'border',
					region:'west',
					title: '',
					width: 550,
					items: [
						tree,
						tree2
					]
					}),
					iframe
			     ]
			});
        }
    };
}();

Ext.onReady(function(){
	
	Ext.MessageBox.buttonText = {
		ok     : "OK",
		cancel : "Abbrechen",
		yes    : "Ja",
		no     : "Nein"
	};
	
	TreeTest.init();
});
</script>
<body>

</body>
</html>