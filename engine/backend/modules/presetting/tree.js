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
		    
           var tree = new Tree.TreePanel( {
           		el: 'tree-div',
		        animate:false, 
		        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getSettings.php'}),
		        containerScroll: true
		    });
		    
		    tree.on('click', function(e){
		    	if (e.attributes.id){
		        
			    	if (!e.attributes.file){
			    		var url = "settingsdetail.php?id="+e.attributes.id;
			        }else {
			        	var url = e.attributes.file;
			        }
			        
			        document.getElementById('settingsList').src = url; 	
		        
		    	}
    		}); 
            
             
            // set the root node
		    var root = new Tree.AsyncTreeNode({
		        text: 'Shopware Konfiguration',
		        draggable:false,
		        id:'0',
		        direct:''
		    });
            tree.setRootNode(root);
			
            // render the tree
            tree.render();
			
            tree.on('contextmenu', this.menuShow, this);
            root.expand();        
       	 	}
    	};
}
();

Ext.onReady(function(){    
    iTree.init();
});

