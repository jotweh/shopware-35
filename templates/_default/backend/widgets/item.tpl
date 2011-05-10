{if 1 != 1}<script>{/if}
Ext.define('Swag.Widget.{$item}',
{
	extend: 'Ext.panel.Panel',
	initComponent: function(){
		Ext.apply(this, {
			html: 'Hello World'
		});
		this.callParent(arguments);
	}
}
);