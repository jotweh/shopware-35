Ext.define('Shopware.PaymentEos.Window', {
	extend: 'Ext.window.Window',
	
	//Default settings
	height: 100,
    width: 400,
    layout: 'fit',
	
	initComponent: function(args){		
		
		//Add Testgrid
		this.grid = Ext.create('Ext.grid.Panel', {
			store: Ext.create('Ext.data.ArrayStore', { }),
			columns: [{ header: 'test' }]
		});
		this.items = [this.grid];
		
		this.callParent(args);
	}
});