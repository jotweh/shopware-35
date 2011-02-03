/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */
 var iTree = function(){
    var root, tree;
    var Tree = Ext.tree;
    
    return {
    	reload : function(){
    		
    		//Tree.root.reload();
    		tree.root.reload();
    		//console.log();
    	},
        init : function(){
            // yui-ext tree
        
		    
           tree = new Tree.TreePanel({
           		el: 'tree-div',
		        animate:false, 
		        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getBrowser.php'}),
		        containerScroll: true,
		        lines:true
		    });
		    
		    tree.on('click', function(e){
		    	if (e.attributes.id){
		        
			    	if (!e.attributes.file){
			    		var url = "options.php?id="+e.attributes.id;
			        }else {
			        	var url = e.attributes.file;
			        }
			        
			        document.getElementById('settingsList').src = url; 	
		        
		    	}
    		}); 
            
             
            // set the root node
		    root = new Tree.AsyncTreeNode({
		        text: 'Shopware',
		        draggable:false,
		        id:'0',
		        direct:''
		    });
            tree.setRootNode(root);
			
            // render the tree
            tree.render();

            root.expand();        
       	 	}
    	};
}
();

Ext.onReady(function(){    
    iTree.init();
});

