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
<title><?php echo $sLang["auth"]["auth_overview"] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../backend/css/modules.css" rel="stylesheet" type="text/css" />
<link href="../../../backend/css/icons.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../../../vendor/ext/resources/css/ext-all.css" />
<script type="text/javascript" src="../../../vendor/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../../../vendor/ext/ext-all.js"></script>
</head>
<style>
.x-node-ctx {
    background:#eee !important;
    border:1px solid #ccc !important;
}.x-tab-panel-header {
    border-bottom-width:0 !important;
}
#main-tabs .x-tab-panel-body {
    background:transparent;
    border:0 none;
}
.x-tree-node .x-tree-selected a span{
	background:transparent;
	color:#15428b;
    font-weight:bold;
}
.x-tree-selected {
    border:1px dotted #a3bae9;
    background:#DFE8F6;
}
.tree {
    border:1px solid #fff;
    margin:3px;
}
.x-tree-node div.feeds-node{
    background:#eee url(images/cmp-bg.gif) repeat-x;
    margin-top:1px;
    border-top:1px solid #ddd;
    border-bottom:1px solid #ccc;
    padding-top:2px;
    padding-bottom:1px;
}

.x-tree-node-leaf .x-tree-node-icon {

    background-image:url();
}
.x-tree-node-leaf .x-tree-node-icon {
	display:none
}

.tree-node .x-tree-node-icon {
    display:none;
}
.x-tree {
    background:#fff !important;
}
</style>
<body>
<script type="text/javascript">
function sWrapper(sFunction, sId){
	switch (sFunction){
		case "deleteArticle":
			// Redirect
			//ndow.location.href = "<?php echo $_SERVER["PHP_SELF"] ?>?group=<?php echo $_GET["group"]?>&delete="+sId;
				Ext.Ajax.request({
	                waitMsg: 'Speichere Seite',
	                url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteSnippet.php', 
	                params: { 
	                    nodeId: sId								
	                },                            
	                success:function(response,options){
						parent.Growl('Baustein wurde gelöscht');
//						myExt.reload();
	                }                                   
            	}); 
			break;
	}
}

function deleteArticle(ev,text){
		if(parseInt(ev))
		{
			var additional = '';
		}else{
			var additional = "<?php echo $sLang["cmsstatic"]["cms_delete_recrusiv"]; ?>";
		}
		parent.sConfirmationObj.show('<?php echo $sLang["cmsstatic"]["cms_should_the_site"] ?> "'+text+'" <?php echo $sLang["cmsstatic"]["cms_really_deleted"] ?>'+'<br><font color=red>'+additional+'</font>',window,'deleteArticle',ev);
}
		/*
	* Version 1.0 alpha
	*
	 * Ext.ux.TreeFilterPluginAllInOne
	 *
	 * @author    Dott. Ing.  Marco Bellocchi
	 * @date     6. Gune 2008
	 * @license Ext.ux.Utility.js is licensed under the terms of the Open Source
	 * LGPL 3.0 license. 
	 * 
	 * License details: http://www.gnu.org/licenses/lgpl.html
	*/
	
	Ext.namespace('Ext.ux');
	//Utility class
	Ext.ux.Utility = {
	
	    isNullOrUndefined: function(obj) {
	        return (typeof obj == 'undefined' || obj == null );
	    },
		isFunction: function(f){
			return typeof f == 'function';
		}
	}
	
	//For more info http://extjs.com/forum/showthread.php?t=37697
	Ext.applyIf(Array.prototype, {
	    /**
	     * Add an element at the specified index
	     * @param {Object} o The object to add
	     * @param {int} index The index position the element has to be inserted
	     * @return {Boolean} True if you can insert
	     */
	    insertAt : function(o, index){    
	        if ( index > -1 && index <= this.length ) {
	            this.splice(index, 0, o);
	            return true;
	        }        
	        return false;
	    },
	     /**
	     * Add an element after another element
	     * @param {Object} The object before which you want to insert
	     * @param {Object} The object to insert
	     * @return {Boolean} True if inserted, false otherwise
	     */
	    insertBefore : function(o, toInsert){
	       var inserted = false;
	       var index = this.indexOf(o);
	       if(index == -1)
	            return false;
	       else {
	           if(index == 0){
	               this.unshift(toInsert)
	               return true;
	           }
	           else
	               return this.insertAt(toInsert, index - 1);
	       }   
	    },
	     /**
	     * Add an element before another element
	     * @param {Object} The object after which you want to insert
	     * @param {Object} The object to insert
	     * @return {Boolean} True if inserted, false otherwise
	     */
	    insertAfter : function(o, toInsert){
	       var inserted = false;
	       var index = this.indexOf(o);
	       if(index == -1)
	            return false;
	       else {
	           if(index == this.length - 1){
	               this.push(toInsert);
	               return true;
	           }
	           else
	               return this.insertAt(toInsert, index + 1);
	        }   
	    }
	}); 
	
	Ext.ux.SearchField = Ext.extend(Ext.form.TwinTriggerField, {
	 initComponent : function(){
		 
		 Ext.ux.SearchField.superclass.initComponent.call(this);
		 this.on('specialkey', function(f, e){
			 if(e.getKey() == e.ENTER){
				this.onTrigger2Click();
			 }
		 }, this);
	 },
	
	 validationEvent:false,
	 validateOnBlur:false,
	 trigger1Class:'x-form-clear-trigger',
	 trigger2Class:'x-form-search-trigger',
	 hideTrigger1:true,
	 hasSearch : false,
	
	 onTrigger1Click : function(){
		 if(this.hasSearch){
		     this.hasSearch = false;
			 this.triggers[0].hide();
			 this.treeFilterPlugin.clearFilter();
			 this.preFocus();
			 this.focus();
		 }
	 },
	
	 onTrigger2Click : function(){
		 var v = this.getRawValue();
		 if(v.length < 1){
			 this.onTrigger1Click();
			 return;
		 }
		 /*
		 if(v.length < 2){
			 Ext.Msg.alert('Invalid Search', 'You must enter a minimum of 2 characters to search');
			 return;
		 }
		 */
		 this.treeFilterPlugin.applyFilter();
		 this.hasSearch = true;
		 this.triggers[0].show();
		 this.focus();
	 }
	});
	
	//Start with filter implementation
	Ext.ux.StartWithTreeFilter = function(){
		var re = null;
		//@Public function called before filter is applied(before the execution of the filterFn;); return true to cancel the execution of the operation.
		this.beforeFilterFn = function(text, treePanel, treeFilter){
			if(Ext.ux.Utility.isNullOrUndefined(text) || text == ''){
				treeFilter.clear();
				return true;
			}
			re = new RegExp('^' + Ext.escapeRe(text), 'i');
			//treePanel.expandAll();
			return false;
		}
		//@Public function called for each node on the tree starting from the root. Return  true if you want to keep the node; all the nodes are processed starting from the root
		this.filterFn = function(node, nodeAttribute, text, treePanel, treeFilter){
			return true;
		}
		//@Public function called after filter after the execution of the filterFn.
		this.afterFilterFn = function(text, treePanel, treeFilter){
			treePanel.loader.baseParams = {search: text};
			treePanel.root.reload();
			re = null;
		}
	}
	
	Ext.ux.TreeFilterPlugin = function(cfg)
	{
	    var textField = null;
	    var filter = null;
		var re = null;
		var treePanel = null;
		
	    var defaultCfg = {
	        toolbarPosition: 'bottom' //top
	        ,insertAt: 0
	        ,width: 200
	        ,label: ''
			,bufferDelay: 350
	        ,treeFilterCfg: {
	        	clearBlank: true
		        ,autoClear: true
	        }
			,emptyText : 'Kein Filter..'
			,textFieldType: 'manual' //'auto'
			,filterInstance: new Ext.ux.StartWithTreeFilter()
			,nodeAttribute: 'text'
	    };
	    Ext.apply(defaultCfg, cfg);
	    
	    this.init = function(tp){
	        treePanel = tp;
			treePanel.on('destroy', function(){
				treePanel = null;
			}, this);
	        var tbar = null;
	        
	        switch(defaultCfg.toolbarPosition) {
	            case 'top':
	                tbar = treePanel.getTopToolbar();   
	                if(Ext.ux.Utility.isNullOrUndefined(tbar)){
	                    tbar = [];
	                    treePanel.elements += ',tbar';
	                    treePanel.topToolbar = tbar;
	                }
	                break;
	            case 'bottom':
	                tbar = treePanel.getBottomToolbar();   
	                if(Ext.ux.Utility.isNullOrUndefined(tbar)) {
	                    tbar = [];
	                    treePanel.elements += ',bbar';
	                    treePanel.bottomToolbar = tbar;
	                }
	                break;
	        }
			var textFieldCfg = {};
			switch(defaultCfg.textFieldType) {
			case 'auto':
		        textFieldCfg = {
		            width: defaultCfg.width
					,emptyText: defaultCfg.emptyText
					,treeFilterPlugin: this
		            ,listeners:{
		                render: function(f){
		                    f.el.on('keydown', f.onTrigger2Click, f, {buffer: defaultCfg.bufferDelay || 350});
		                }
						,scope: this
		            }
				};
				break;
			case 'manual':
				textFieldCfg = {
		            width: defaultCfg.width
					,emptyText: defaultCfg.emptyText
					,treeFilterPlugin: this
				};
				break;
			}
			textField = new Ext.ux.SearchField(textFieldCfg);
	
	        filter = new Ext.tree.TreeFilter(treePanel, defaultCfg.treeFilterCfg);   
			
	        tbar.insertAt(textField, defaultCfg.insertAt);
	        
			if(!Ext.ux.Utility.isNullOrUndefined(defaultCfg.label) && defaultCfg.label != '')
	            tbar.insertBefore(textField, defaultCfg.label);
	    }
	        
		
		this.getTreeFilter = function(){
			return filter;
		}
		
		this.getTextField= function(){
			return textField;
		}
		
		this.applyFilter = function(){
			var text = textField.getValue();
			var cancel = defaultCfg.filterInstance.beforeFilterFn(text, treePanel, filter);
			if(cancel === true)
				return;		
			filter.filterBy(function(n){
				return defaultCfg.filterInstance.filterFn(n, defaultCfg.nodeAttribute || 'text', text, treePanel, filter);
			}, defaultCfg.filterInstance);
			defaultCfg.filterInstance.afterFilterFn(text, treePanel, filter);
		}
	    //Clear the filter and the textField value
	    this.clearFilter = function(){
	       filter.clear();
	       if(!Ext.ux.Utility.isNullOrUndefined(textField.el))
				textField.el.dom.value = '';
		   textField.applyEmptyText();
	    }
}
var myExt = function(){
		var store;
		var storeid;
		var myTab;
		var tree;

	return {
	reload : function(){
		tree.loader.baseParams = {search: ''};
    	tree.root.reload();
    },
	init : function(){
	
	   	Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
	
		Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
		onRender : function(ct, position){
		  this.el = ct.createChild({tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
		}
		}); 
  	    var treeFilterPlugin = new Ext.ux.TreeFilterPlugin({
	        toolbarPosition:'bottom'
	        ,width: '170'
	        ,label: 'Suchen: '
    	});
		tree = new Ext.tree.TreePanel({
		id:'feed-tree',
		region:'west',
		title:'Bausteine',
		split:true,
		width: 225,
		minSize: 175,
		maxSize: 400,
		collapsible: true,
		margins:'0 0 0 0',
		cmargins:'0 5 5 5',
		rootVisible:false,
		plugins: [treeFilterPlugin],
		lines:false,
		loader: new Ext.tree.TreeLoader({dataUrl:'../../../backend/ajax/getSnippets.php'}),
		autoScroll:true,
		root: new Ext.tree.AsyncTreeNode({
		 text: '<?php echo $sLang["auth"]["auth_user"] ?>',
		 cls:'feeds-node',
		 expanded:true,
		 id:'0'
		}),
		collapseFirst:false,
		
		tbar: [
		{
		    iconCls:'add-feed',
		    text:'Hinzufügen',
		    handler: showWindow,
		    scope: this
		},{
		    id:'delete',
		    text:'Löschen',
		    handler: function(){
		    	
		    	
		        var s = tree.getSelectionModel().getSelectedNode();
		        if(s){
		            deleteArticle(s.attributes.id,s.attributes.text);
		        }else{
		        	Ext.Msg.alert('', '<?php echo $sLang["cmsstatic"]["cms_no_selection"] ?>');
		        }
		    },
		    scope: this
		},{
			id:'refrsh',
			text:'',
			iconCls: 'x-tbar-loading',
			handler: function(){
				myExt.reload();
			}
		}]
		});
	    
	    tree.on('click', function(e){
	    	if (e.attributes.leaf){
	 
			 var url = "snippets.php?edit="+e.attributes.id;
			 Ext.get('framepanelmyiframe').dom.src = url;
			 parentNode = e.parentNode;
			 currentNode = e;
	    	}
	    });  
	    
	    var iframe = new Ext.ux.IFrameComponent({ 
			region:'center',
			split:true,
			animate:true, 
			fitToFrame: true,
			title:'<?php echo $sLang["auth"]["auth_details"] ?>',
			width:700,
	        height:500,
			collapsible: true,
			id: "myiframe", 
			url: 'bulk.php' 
		});
			
		function showWindow(){
			 var url = "snippets.php?new=1";
			 Ext.get('framepanelmyiframe').dom.src = url;
		}
		function removeUser(id){
			Ext.MessageBox.confirm('<?php echo $sLang["auth"]["auth_validation"] ?>', '<?php echo $sLang["auth"]["auth_really_delete_marked_user"] ?>', function deleteClientConfirmed(btn,id){
	    		if (btn=="yes"){
	    				var user = tree.getSelectionModel().getSelectedNode().attributes.id;
	    				
	    				if (user){
	    					
							Ext.Ajax.request({
			                    waitMsg: '<?php echo $sLang["auth"]["auth_saving_changes"] ?>',
			                    url: '<?php echo $_SERVER["SERVER_PORT"] == "80" ? "http" : "https" ?>://<?php echo $sCore->sCONFIG['sBASEPATH']?>/engine/backend/ajax/deleteAuth.php', 
			                    params: { 
			                        user: user								
			                    },
			                    failure:function(response,options){
			                        Ext.MessageBox.alert('<?php echo $sLang["auth"]["auth_warning"] ?>','<?php echo $sLang["auth"]["auth_oops"] ?>');
			                    },                             
			                    success:function(response,options){
									parent.Growl('<?php echo $sLang["auth"]["auth_user"] ?> '+user+' <?php echo $sLang["auth"]["auth_deleted"] ?>');
									myExt.reload();
			                    }                                   
			            	}); 
	    				}
			    	}
    	})};
	     
		myTab = new Ext.TabPanel({
		    region:'center',
		    deferredRender:false,
		    activeTab:0,
		    closeable:true
		});
		
		var viewport = new Ext.Viewport({
		layout:'border',
		items:[
		    tree,iframe
		 ]
		});
       
       
         
}};
}();
Ext.onReady(function(){
	myExt.init();
});
</script>

</body>
</html>
