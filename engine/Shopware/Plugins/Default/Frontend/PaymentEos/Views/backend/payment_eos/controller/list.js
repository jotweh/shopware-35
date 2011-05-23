Ext.define('PaymentEos.controller.List', {
	
	extend: 'Ext.app.Controller',
	
	views: [
		'Viewport',
    	'List',
    	'Detail'
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
    	this.listView = this.getListView().create({
			statusStore: this.getStatusStore().load(),
			store: this.getListStore().load(),
			region: 'center'
		});
		
		this.detailView = this.getDetailView().create({
			region: 'east',
			listView: this.listView
		});
    	
    	this.getView('Viewport').create({
    		items: [this.listView, this.detailView]
    	});
    	
    	this.listView.getSelectionModel().on('selectionchange', function(sm, records) {
            if (records.length) {
            	this.detailView.updateDetail(records[0]);
            }
        }, this);
    }
});