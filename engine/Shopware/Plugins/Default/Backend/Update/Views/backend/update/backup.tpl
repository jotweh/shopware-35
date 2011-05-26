<script type="text/javascript">
Backup = Ext.extend(Ext.FormPanel, {
	title: 'Backup-Verwaltung',
	closable: false,
	autoScroll: true,
	layout: 'fit',
	initComponent: function() {
		this.List = new Shopware.Update.BackupList();
		this.items = [this.List];
		this.buttons = [{
	        text: 'Datenbank-Backup erstellen',
	        handler: function(){
	        	var form = this.getForm();
	            if(!form.isValid()) {
	            	return;
	            }
	            var formConfig = {
	            	url: '{url action=backupDatabase}',
	            	success: function(form, action){
	            		if(action.result.tables) {
	            			Ext.MessageBox.wait(action.result.message, 'Backup');
	            			formConfig.params = action.result;
	            			form.submit(formConfig);
	            		} else {
	            			Ext.MessageBox.alert('Backup', action.result.message);
	            			this.List.refreshList();
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
	    }, {
	        text: 'Liste aktualisieren',
	        handler: function(){
	        	this.List.refreshList();
	        },
	        scope: this
	    }];
        Backup.superclass.initComponent.call(this);
	}
});
Shopware.Update.Backup = Backup;
</script>