Ext.define('PaymentEos.controller.List', {
	
	extend: 'Ext.app.Controller',
	
	views: [
		'Viewport',
    	'List'
    ],
    
    models: [
    	'List'
    ],
    
    stores: [
    	'List'
    ],
    
    init: function() {
    	//console.log(this.getView('Viewport'));
    	
    	//this.getView('Viewport').add(
    	//	this.getView('List').create()
    	//);
    	    	
    	this.getView('Viewport').create({
    		items: this.getListView().create({
    			store: this.getListStore()
    		})
    	});
    	
    	//console.log(this.getView('List'));
    }
});