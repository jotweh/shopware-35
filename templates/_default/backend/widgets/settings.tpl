{if 1 != 1}<script>{/if}
Ext.define('Swag.Config.{$load}',
{
	extend: 'Ext.form.FieldContainer',
	initComponent: function(){
		
		Ext.apply(this, {
			autoHeight:true,
			items: [
				{
					xtype: 'hiddenfield',
					name: 'widgetName',
					value: '{$widget.name}'
				},
				{
					xtype: 'hiddenfield',
					id: 'widgetUid',
					name: 'widgetUid',
					value: '{$widget.uid}'
				},
				new Ext.form.FieldSet({
					title: 'Info',
					padding: '10 10 10 10',
					margin:'10 10 10 10',
					frame: true,
					html: '{$widget.label}'
				}),
				new Ext.form.FieldSet({
					title: 'Widget-Konfiguration',
					padding: '10 10 10 10',
					margin:'10 10 10 10',
					frame: true,
					items: [
					{if $widget.configuration}
						{foreach $widget.configuration as $element}
							{$value = $element.value}
							{
								{if $element.type == 'text'}
									xtype: 'textfield',
								{elseif $element.type=='radiogroup'}
									xtype: 'radiogroup',
								{elseif $element.type=='textarea'}
									xtype: 'textarea',
								{elseif $element.type=='radio'}
									xtype: 'radio',
								{elseif $element.type=='password'}
									xtype: 'password',
								{else}
								{/if}
								{if $element.type=='checkbox'}
									columns: [100, 100],
									items: [
										{ boxLabel: 'Yes', name: 'config[{$element.name}]', inputValue: 1, checked: {if $value}true{else}false{/if} },
										{ boxLabel: 'No', name: 'config[{$element.name}]', inputValue: 0, checked: {if !$value}true{else}false{/if} },
									],
								{else}
									value: '{$value|escape:"javascript"}',
								{/if}
								fieldLabel: '{if $element.label}{$element.label}{else}{$element.name|ucfirst}{/if}',
								name: 'config[{$element.name}]',
								labelWidth:250,
								disabled: {if $element.isDisabled}true{else}false{/if},
								width: 450,
								allowBlank: {if $element.isRequired}false{else}true{/if}
							}{if !$element@last},{/if}
						{/foreach}
					{/if}
					]
				})
			]
		});
		this.callParent(arguments);
	}
}
);
{if 1 != 1}</script>{/if}