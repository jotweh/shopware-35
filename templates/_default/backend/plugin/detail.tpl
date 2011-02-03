{*<script>//*}
{$callback|escape:'javscript'}({
	xtype: 'panel',
	closable: true,
	autoScroll:true,
	id: 'plugin{$plugin.id}',
	title: 'Plugin ({$plugin.label})',
	items: [
		{
			xtype: 'form',
			bodyStyle:'padding:10px',
            layout: 'form',
			title: 'Informationen',
			height: 170,
			autoScroll: true,
			defaults: { anchor: '100%' },
	        labelWidth: 120,
			items: [
				{
					xtype: 'textfield',
					fieldLabel: 'Hersteller',
					value: '{$plugin.autor|escape:"javascript"}',
					readOnly: true
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Version',
					value: '{$plugin.version|escape:"javascript"}',
					readOnly: true
				},
				{
					xtype: 'textfield',
					fieldLabel: 'Copyright',
					value: '{$plugin.copyright|escape:"javascript"}',
					readOnly: true
				}
			]
		},
{if $plugin.description}
		{
			title: 'Beschreibung',
			xtype: 'panel',
			height: 150,
			autoScroll: true,
			//frame: true,
			bodyStyle:'padding:10px',
			html: '{$plugin.description|escape:"javascript"}',
		},
{/if}
		{
			xtype: 'form',
			id: 'plugin_form{$plugin.id}',
			baseParams: { id:{$plugin.id} },
	        labelWidth: 180,
	        layout: 'form',
	        height:400,
	        autoScroll:true,
	        title: 'Einstellungen',
	        bodyStyle: 'padding:5px',
			items: [
				{
					xtype: 'radiogroup',
					fieldLabel: 'Aktiv',
					name: 'active',
					columns: [100, 100],
		            items: [
		                { boxLabel: 'Ja', name: 'active', inputValue: 1, checked: {if $plugin.active}true{else}false{/if} },
		                { boxLabel: 'Nein', name: 'active', inputValue: 0, checked: {if !$plugin.active}true{else}false{/if} },
		            ]
				},
{if $form && $form->getElements()}
				{
					xtype: 'tabpanel',
		            activeTab: 0,
				    enableTabScroll: true,
		            autoScroll: true,
		            deferredRender: false,
					items: [
		{foreach $shops as $shop}
						{
							title: '{$shop.name|escape:"javascript"}',
							bodyStyle: 'padding:10px',
							defaults: { anchor: '100%' },
							layout: 'form',
							items: [

				{foreach $form->getElements() as $element}
				{$value = $plugin_config[$shop.id][$element->getName()]|default:$element->getValue()}
								{
				{if $element->getType()=='Zend_Form_Element_Text'}
									xtype: 'textfield',
				{elseif $element->getType()=='Zend_Form_Element_Checkbox'}
									xtype: 'radiogroup',
				{elseif $element->getType()=='Zend_Form_Element_Textarea'}
									xtype: 'textarea',
				{elseif $element->getType()=='Zend_Form_Element_Radio'}
									xtype: 'radio',
				{elseif $element->getType()=='Zend_Form_Element_Password'}
									xtype: 'password',
				{else}
				{/if}
				{if $element->getType()=='Zend_Form_Element_Checkbox'}
									columns: [100, 100],
						            items: [
						                { boxLabel: 'Ja', name: 'config[{$shop.id}][{$element->getName()}]', inputValue: 1, checked: {if $value}true{else}false{/if} },
						                { boxLabel: 'Nein', name: 'config[{$shop.id}][{$element->getName()}]', inputValue: 0, checked: {if !$value}true{else}false{/if} },
						            ],
				{else}
									value: '{$value|escape:"javascript"}',
				{/if}
									fieldLabel: '{if $element->getLabel()}{$element->getLabel()}{else}{$element->getName()|ucfirst}{/if}',
									name: 'config[{$shop.id}][{$element->getName()}]',
									//boxLabel: 'BoxLabel',
									disabled: {if $element->scope||$shop.default}false{else}true{/if},
									allowBlank: {if $element->isRequired()}false{else}true{/if}
									
								}{if !$element@last},{/if}
				{/foreach}
				
							]
						}{if !$shop@last},{/if}
		{/foreach}
					]
				}
{/if}
			],
	        buttonAlign:'right',
	        buttons: [{
	            text: 'Speichern',
	            handler: function(){
	            	var form = Ext.getCmp('plugin_form{$plugin.id}').getForm();
		            form.submit({ url:'{url action="saveDetail"}', waitMsg:'Speichern...', success: function (el, r){

		            } });
		        }
	        }]
		}
	]
});