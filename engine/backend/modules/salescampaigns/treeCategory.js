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
		        loader: new Tree.TreeLoader({dataUrl:'../../../backend/ajax/getCategories.php'}),
		        enableDD:true,
		        enableEdit: true,
		        containerScroll: true
		    });
            tree.on('click', function(e){
		        var url = "start.php?id="+e.attributes.id;
		        document.getElementById('articleList').src = url; 
				
				
    		});  
            // set the root node
		    var root = new Tree.AsyncTreeNode({
		        text: 'Shopware',
		        draggable:false,
		        id:'1'
		    });
            tree.setRootNode(root);
			
            // render the tree
            tree.render();
			
           
            root.expand();        
       	 	}};
}
();

Ext.onReady(function(){    
    iTree.init();
});
