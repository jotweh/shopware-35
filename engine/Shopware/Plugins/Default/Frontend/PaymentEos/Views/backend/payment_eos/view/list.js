Ext.define('PaymentEos.view.List', {

	extend: 'Ext.grid.Panel',
	
	title: 'Zahlungen',
	layout: 'fit',
	viewConfig: {
        stripeRows: true
    },
    
    columns: [{
        text     : 'Datum',
        width    : 85,
        sortable : true,
        xtype    : 'datecolumn',
        dataIndex: 'added'
    },{
        text     : 'Bestellnummer',
        width    : 85,
        sortable : true,
        dataIndex: 'order_number'
    },{
        text     : 'Transaktions-ID',
        width     : 85,
        sortable : true,
        dataIndex: 'transactionID'
    },{
        text     : 'Zahlungsart',
        width    : 120,
        sortable : true,
        dataIndex: 'payment_description'
    },{
        text     : 'Kunde',
        width    : 120,
        sortable : true,
        dataIndex: 'customer'
    },{
        text     : 'Reservierter Betrag',
        width    : 120,
        sortable : true,
        align    : 'right',
        renderer : function(value, column, model) {
        	return model.data.amount_format;
        },
        dataIndex: 'amount'
    },{
        text     : 'Gebuchter Betrag',
        width    : 120,
        sortable : true,
        align    : 'right',
        renderer : function(value, column, model) {
        	return model.data.book_amount_format;
        },
        dataIndex: 'book_amount'
    },{
        text     : 'EOS-Status',
        width    : 100,
        sortable : true,
        dataIndex: 'clear_status',
        renderer : function(value, column, model) {
        	return this.statusStore.findRecord('id', value).get('name');
        }
    },{
        xtype:'actioncolumn', 
        width:50,
        items: [{
            icon: "{link file='engine/backend/img/default/icons/user.png'}",
            iconCls: 'action_icon',
            tooltip: 'Kundenkonto öffnen',
            handler: function(grid, rowIndex, colIndex) {
                var record = grid.getStore().getAt(rowIndex);
                parent.loadSkeleton('userdetails', false, { 'user': record.get('userID') });
            }
        }, {
            icon: "{link file='engine/backend/img/default/icons4/sticky_notes.png'}",
            iconCls: 'action_icon',
            tooltip: 'Bestellung öffnen',
            handler: function(grid, rowIndex, colIndex) {
            	 var record = grid.getStore().getAt(rowIndex);
                parent.loadSkeleton('orders', false, { 'id': record.get('orderID') });
            }
        }, {
            iconCls: 'delete',
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