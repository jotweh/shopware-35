<script type="text/javascript">
Handler = Ext.extend(Ext.FormPanel, {
	title: 'Update starten',
	closable: false,
	defaults: { anchor: '100%', xtype:'textfield' },
	layout:'form',
    labelWidth: 300,
    bodyStyle:'padding:20px',
	autoScroll: true,
	initComponent: function() {
		
		this.fieldsetBase = {
            xtype:'fieldset',
            title: 'Update-Einstellungen',
            autoHeight:true,
            defaults: { anchor: '100%' },
            defaultType: 'textfield',
            items :[{
		        xtype: 'hidden',
		        name: 'hash',
		        value: '{$VersionConfig->hash}'
		    }, {
				fieldLabel: 'Version',
				name: 'version',
				value: '{$VersionConfig->version}',
				readOnly: true,
				disabled: true
			}, {
				fieldLabel: 'Version',
				name: 'version',
				value: '{$VersionConfig->version}',
				readOnly: true,
				disabled: true
			}, {
				xtype: 'checkbox',
				fieldLabel: 'Cache leeren',
				name: 'cache',
				checked: true
			}, {
				xtype: 'checkbox',
				fieldLabel: 'Wartungsmodus aktivieren',
				name: 'service_mode',
				checked: true
			}, {
				xtype: 'checkbox',
				fieldLabel: 'Datenbank-Backup erstellen',
				name: 'backup',
				checked: true
			}]
		};
		
		this.formConfig = {
        	url: '{url}',
        	success: function(form, action){
        		if(action.result.action) {
        			Ext.MessageBox.wait(action.result.message, 'Update');
        			this.formConfig.params = Update.ConfigForm.getForm().getValues();
        			Ext.apply(this.formConfig.params, action.result);
        			form.submit(this.formConfig);
        		} else {
        			Ext.MessageBox.alert('Update', action.result.message);
        			Update.Backup.List.refreshList();
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
		this.items = [this.fieldsetBase];
		this.buttons = [{
	        text: 'Start',
	        handler: function() {
	        	var form = this.getForm();
	            if(!form.isValid()) {
	            	return;
	            }
	            if(!Update.ConfigForm.rendered) {
	            	Update.Tabs.setActiveTab(Update.ConfigForm);
	            	return;
	            } else if(!Update.ConfigForm.getForm().isValid()) {
	            	Update.Tabs.setActiveTab(Update.ConfigForm);
	            	return;
	            }
	            Ext.MessageBox.wait('Bitte warten ...', 'Update');
	            this.formConfig.params = { action: 'update' };
	            form.submit(this.formConfig);
	        },
	        scope: this
	    }];
        Handler.superclass.initComponent.call(this);
	}
});
Shopware.Update.Handler = Handler;
</script>