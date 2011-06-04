{if 1 != 1}<script>{/if}
Ext.define('Ext.app.Monitor.Form',
{
	extend: 'Ext.form.Panel',
    height: 400,
	region: 'south',
	id: 'form',
    initComponent: function(){
		 this.items =
		 [
				  {
                xtype: 'fieldcontainer',
                combineErrors: true,
                msgTarget : 'side',
                layout: 'hbox',
                defaults: {
                    flex: 1,
                    hideLabel: true
                },
                items: [
                   {
                    xtype: 'textareafield',
                    fieldLabel: 'Query',
					name: 'query',
                    labelAlign: 'top',
                    flex: 1,
					border: 1,
					height: 200,
					width: 500,
                    margins: '0',
                    allowBlank: false
                },
				{
                    xtype: 'textareafield',
                    fieldLabel: 'Parameter',
					name: 'parameters',
                    labelAlign: 'top',
                    flex: 1,
					border: 1,
					height: 200,
					width: 500,
                    margins: '0',
                    allowBlank: false
                }
                ]
            }

	     ];
		 this.callParent(arguments);
	}
});