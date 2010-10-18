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
		    
		    var ge = new Ext.tree.TreeEditor(tree, {
                allowBlank:false,
                blankText:'A name is required',
                selectOnFocus:true
               
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
							parent.Growl('Verbindung zum Server nicht möglich');
							return false;
						}else {
							parent.Growl('Kategorie wurde umbenannt');
							return false;
						}
								
					}
					});
                });
            
            tree.on('click', function(e){
		        var url = "banner_inline.php?id="+e.attributes.id;
		        document.getElementById('promotion_inline').src = url; 
				
				
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
       	 	}
    	};
}
();

Ext.onReady(function(){    
    iTree.init();
});
