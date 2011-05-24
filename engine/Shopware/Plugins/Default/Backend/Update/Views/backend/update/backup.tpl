<script type="text/javascript">
Backup = Ext.extend(Ext.FormPanel, {
	title: 'Backup erstellen',
	closable: false,
	autoScroll: true,
    defaults: { anchor: '100%', xtype:'textfield' },
    fileUpload: true,
	layout:'form',
    labelWidth: 120,
    bodyStyle:'padding:20px',
	initComponent: function() {
		
		this.fieldsetBase = {
            xtype:'fieldset',
		    title: 'Tabellen',
		    layout: 'fit',
		    items: [{
	            xtype: 'multiselect',
	            name: 'tables',
	            hiddenField: 'tables',
				dataFields: ['id', 'name'],
				data:  [{foreach $TableCounts as $Table=>$Count}
					['{$Table|escape:javascript}', '{$Table|escape:javascript} ({$Count})']{if !$Count@last},{/if}
				{/foreach}],
				valueField: 'id',
				displayField: 'name',
				width: 'auto',
				height: 'auto',
				//allowBlank: false,
				value: '{foreach $TableCounts as $Table=>$Count}{$Table}{if !$Count@last},{/if}{/foreach}'
	        }]
		};
		this.items = [this.fieldsetBase];
		
		this.buttons = [{
	        text: 'Erstellen',
	        handler: function(){
	        	var form = this.getForm();
	            if(!form.isValid()) {
	            	return;
	            }
	            var formConfig = {
	            	url: '{url action=backup}',
	            	success: function(form, action){
	            		if(action.result.tables) {
	            			Ext.MessageBox.wait(action.result.message, 'Backup');
	            			formConfig.params = action.result;
	            			form.findField().setValue(null);
	            			form.submit(formConfig);
	            		} else {
	            			Ext.MessageBox.alert('Backup', action.result.message);
	            			Update.BackupList.refreshList();
	            		}
	            	},
	            	failure: function(form, action) {
	            		switch (action.failureType) {
	            			case Ext.form.Action.CLIENT_INVALID:
	            				Ext.Msg.alert('Fehler', 'Bitte überprüfen Sie Ihre Eingaben.');
	            				break;
	            			case Ext.form.Action.CONNECT_FAILURE:
	            				Ext.Msg.alert('Fehler', 'Ein unbekannter Fehler ist aufgetreten.');
	            				break;
	            			case Ext.form.Action.SERVER_INVALID:
	            			default:
	            				Ext.Msg.alert('Fehler', action.result.message);
	            				break;
	            		}
	            	},
	        		scope: this
	            };
	            Ext.MessageBox.wait('Backup', 'Bitte warten ...'); 
	            form.submit(formConfig);
	        },
	        scope: this
	    }];
		
        Backup.superclass.initComponent.call(this);
	}
});
Shopware.Update.Backup = Backup;
</script>