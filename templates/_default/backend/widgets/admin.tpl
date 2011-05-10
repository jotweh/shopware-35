{if 1 != 1}<script>{/if}
Ext.define('Swag.Admin.{$load}',
{
	extend: 'Ext.form.FieldContainer',
	initComponent: function(){
		Ext.apply(this, {
			autoHeight:true,
			items: [

				new Ext.form.FieldSet({
					title: 'Info',
					padding: '10 10 10 10',
					margin:'10 10 10 10',
					frame: true,
					html: '{$widget.label}'
				}),
				Ext.create('Ext.form.FieldSet',{
					title: 'Zugriff konfigurieren',
					padding: '10 10 10 10',
					margin:'10 10 10 10',
					frame: true,
					items:
					[
						{
							xtype: 'hiddenfield',
							name: 'name',
							value: '{$widget.name}'
						},
						{
							xtype      : 'fieldcontainer',
							fieldLabel : 'Zugriff',
							defaultType: 'radiofield',
							defaults: {
								flex: 1
							},
							layout: 'hbox',
							items: [
								{
									boxLabel  : 'Jeder',
									name      : 'usergroup',
									inputValue: '0'{if $widget.permissions.aclGroup == 0},
									checked: true
									{/if}
								}, {
									boxLabel  : 'Administratoren',
									name      : 'usergroup',
									inputValue: '1'{if $widget.permissions.aclGroup == 1},
									checked: true
									{/if}
								},
								{
									boxLabel  : 'Auswahl',
									name      : 'usergroup',
									inputValue: '2',
									listeners: {
										change: function (field,n,o){
											if (o != true){
												Ext.getCmp('employeeDiv').enable();
											}else {
												Ext.getCmp('employeeDiv').disable();
											}
										}
									}{if $widget.permissions.aclGroup == 2},
									checked: true
									{/if}
								}
							]
						},
						{
							xtype      : 'fieldcontainer',
							id		   : 'employeeDiv',
							fieldLabel : 'Mitarbeiter',
							{if $widget.permissions.aclGroup != 2}
							disabled   : true,
							{/if}
							defaultType: 'checkboxfield',
							items: [
								{foreach from=$users item=user}
								{
									boxLabel  : '{$user.username|escape}',
									name      : 'users[]',
									inputValue: '{$user.id}'{if $user.id|in_array:$selectedUsers},
									checked:	true
									{/if}
								}{if !$user@last},{/if}
								{/foreach}
							]
						}
					]
				})
			]
		});
		this.callParent(arguments);
	}
}
);
{if 1 != 1}</script>{/if}