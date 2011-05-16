Ext.define('PaymentEos.model.OrderPayment', {
    extend: 'Ext.data.Model',    
    fields: [
    	'id', 'transactionID', 'reference', 'account_number',
    	'status', 'clear_status', 'added', 'amount', 'currency'
    ],
    proxy: {
        type: 'rest',
        url : '{url action=getOrderPayment}',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});

