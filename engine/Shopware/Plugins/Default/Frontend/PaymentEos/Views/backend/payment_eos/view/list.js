Ext.define('PaymentEos.view.List', {

	extend: 'Ext.grid.Panel',
	
	title: 'Users',
	layout: 'fit',
	viewConfig: {
        stripeRows: true
    },
    //<tpl if="age > 1 && age < 10">Child</tpl>
    columns: [{
        text     : 'Datum',
        width    : 85,
        sortable : true,
        xtype    : 'datecolumn',
        dataIndex: 'added'
    },{
        text     : 'Transaktion',
        width     : 85,
        sortable : true,
        dataIndex: 'transactionID'
    },{
        text     : 'Kunde',
        width    : 200,
        sortable : true,
        dataIndex: 'customer'
    },{
        text     : 'Referenz',
        width    : 200,
        sortable : true,
        dataIndex: 'reference'
    },{
        text     : 'Betrag',
        width    : 75,
        sortable : true,
        renderer : function(value, column, model) {
        	return Ext.util.Format.currency(value, model.data.currency);
        },
        dataIndex: 'amount'
    },{
        text     : 'Kontonummer',
        width    : 85,
        sortable : true,
        dataIndex: 'account_number'
    },{
        text     : 'EOS-Status',
        width    : 85,
        sortable : true,
        dataIndex: 'clear_status',
        renderer : function(value, column, model) {
        	return this.statusStore.findRecord('id', value).get('name');
        }
    },{
        xtype:'actioncolumn', 
        width:50,
        items: [{
            iconCls: 'ico edit',
            tooltip: 'Edit',
            handler: function(grid, rowIndex, colIndex) {
                var rec = grid.getStore().getAt(rowIndex);
                alert("Edit " + rec.get('firstname'));
            }
        },{
            iconCls: 'ico delete',
            tooltip: 'Delete',
            handler: function(grid, rowIndex, colIndex) {
                var rec = grid.getStore().getAt(rowIndex);
                alert("Terminate " + rec.get('firstname'));
            }                
        }]
    }],
    
    initComponent: function() {
    	
    	this.onTextFieldChange = function() {
    		var value = this.searchField.getValue();
    		this.store.filter('search', value);
    	};
    	
    	this.searchField = Ext.create('Ext.form.field.Text', {
             xtype: 'textfield',
             name: 'searchField',
             hideLabel: true,
             width: 200,
             listeners: {
                 change: {
                     fn: this.onTextFieldChange,
                     scope: this,
                     buffer: 100
                 }
             }
        });
    	
    	this.dockedItems = [{
	        xtype: 'pagingtoolbar',
	        store: this.store,
	        dock: 'bottom',
	        displayInfo: true,
	        items: [
	        	'|', this.searchField
	        ]
	    }];
    	
        this.callParent();
    }
});