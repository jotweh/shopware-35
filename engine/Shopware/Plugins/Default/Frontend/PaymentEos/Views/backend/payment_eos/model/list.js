Ext.define('PaymentEos.model.List', {
	extend: 'Ext.data.Model',
	fields: [
		{ name: 'id', type: 'int' },
		{ name: 'userID',  type: 'string' },
		{ name: 'werbecode', type: 'string' },
		{ name: 'transactionID', type: 'string' },
		{ name: 'secret', type: 'string' },
		{ name: 'reference', type: 'string' },
		{ name: 'account_number', type: 'string' },
		{ name: 'account_expiry', type: 'string' },
		{ name: 'fail_message', type: 'string' },
		{ name: 'status', type: 'string' },
		{ name: 'clear_status', type: 'string' },
		{ name: 'book_date', type: 'string' },
		{ name: 'book_amount', type: 'string' },
		{ name: 'added', type: 'date', dateFormat: 'c'},
		{ name: 'changed', type: 'date', dateFormat: 'c'},
		{ name: 'currency', type: 'string' },
		{ name: 'amount', type: 'float' },
		{ name: 'customer', type: 'string' },
	]
});