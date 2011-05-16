Ext.define('PaymentEos.view.List', {

	extend: 'Ext.grid.Panel',
	
	title: 'Users',
	layout: 'fit',
	viewConfig: {
        stripeRows: true
    },
    
    columns: [{
        text     : 'Company',
        flex     : 1,
        sortable : false,
        dataIndex: 'company'
    },{
        text     : 'Price',
        width    : 75,
        sortable : true,
        renderer : 'usMoney',
        dataIndex: 'price'
    },{
        text     : 'Change',
        width    : 75,
        sortable : true,
        //renderer : change,
        dataIndex: 'change'
    },{
        text     : '% Change',
        width    : 75,
        sortable : true,
        //renderer : pctChange,
        dataIndex: 'pctChange'
    },{
        text     : 'Last Updated',
        width    : 85,
        sortable : true,
        //renderer : Ext.util.Format.dateRenderer('m/d/Y'),
        dataIndex: 'lastChange'
    }]
});