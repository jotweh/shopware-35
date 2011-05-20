Ext.define('PaymentEos.store.List', {
	extend: 'Ext.data.Store',
	model: 'PaymentEos.model.List',
	proxy: {
        type: 'ajax',
        url : '{url action=list}',
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    autoLoad: true,
    remoteSort: true,
    remoteFilter: true
});