Ext.define('PaymentEos.store.Status', {
	extend: 'Ext.data.Store',
	model: 'PaymentEos.model.Status',
	proxy: {
        type: 'ajax',
        url : '{url action=status}',
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    autoLoad: true
});