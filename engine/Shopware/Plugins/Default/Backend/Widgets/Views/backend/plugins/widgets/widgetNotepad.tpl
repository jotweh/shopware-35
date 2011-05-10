{if 1 != 1}<script>{/if}
Ext.define('Swag.Widget.{$item}',
{
	extend: 'Ext.form.Panel',
	    initComponent: function(){
			Ext.apply(this, {
				frame:true,
				height: 250,
				listeners: {
					'afterrender': function(form){
						form.load({ url: '{url controller=WidgetDataStore action=loadNotes}'});
					}
				}
			});


			this.items =
			{
				xtype: 'htmleditor',
				name: 'notes',
				height:200,
				hideLabel:true
			};

			

			this.buttons = [{
				text: 'Speichern',
				handler: function(e){
					this.up('form').getForm().submit(
					{
						url: '{url controller=WidgetDataStore action=saveNotes}',
						success: function(form, action){

						},
						failure: function(form,action){
							Ext.Msg.alert('Failed', action.result.msg);

						},
						scope: this
					});
				}
			}];

			//this.up('form').getForm().load({ url: '{url controller=WidgetDataStore action=loadNotes}'});
			this.callParent(arguments);
		}
}
);