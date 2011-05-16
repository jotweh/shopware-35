Ext.define('PaymentEos.store.List', {

	extend: 'Ext.data.JsonStore',
	
	model: 'PaymentEos.model.List'/*,
    proxy: {
        type: 'ajax',
        url: '{url action=getList}',
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    proxy: {
    type: 'ajax',
    api: {
        read: 'data/users.json',
        update: 'data/updateUsers.json'
    },
    reader: {
        type: 'json',
        root: 'users',
        successProperty: 'success'
    }
}
    autoLoad: true
    */
});