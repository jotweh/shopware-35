Ext.define('PaymentEos.controller.List', {
	
	extend: 'Ext.app.Controller',
	
	views: [
		'Viewport',
    	'List'
    ],
    
    models: [
    	'List',
    	'Status'
    ],
    
    stores: [
    	'List',
    	'Status'
    ],
    
    init: function() {
    	//console.log(this.getView('Viewport'));
    	
    	//this.getView('Viewport').add(
    	//	this.getView('List').create()
    	//);
    	    	
    	this.getView('Viewport').create({
    		items: this.getListView().create({
    			statusStore: this.getStatusStore().load(),
    			store: this.getListStore().load()
    		})
    	});
    	
    	//console.log(this.getView('List'));
    }
});