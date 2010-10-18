<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var Upload = Ext.extend(Ext.Panel, {
	    title: 'Plugins verwalten',
	    initComponent: function() {
	    	
	    	this.upload = new Ext.FormPanel({
			    title: 'Plugin hochladen',
			    id: 'plugin_upload_form',
			    defaults: { anchor: '100%', xtype:'textfield' },
			    fileUpload: true,
				layout:'form',
		        labelWidth: 300,
		        bodyStyle:'padding:20px',
			    items: [{
		            xtype: 'fileuploadfield',
		            emptyText: 'Bitte wählen...',
		            fieldLabel: 'Datei',
		            allowBlank: false,
		            name: 'file',
		            buttonText: '',
		            buttonCfg: {
		                iconCls: 'upload-icon'
		            }
		        }],
		        buttons: [{
		            text: 'Start',
		            handler: function(){
		            	var form = Ext.getCmp('plugin_upload_form').getForm();
		                if(!form.isValid()) return;
		                Ext.MessageBox.wait("","Bitte warten ..."); 
		                form.submit({
		                	url: '{url action="upload"}',
		                	success: function(fp, o){
		                		Ext.MessageBox.alert("Upload erfolgreich!", ""); 
		                	},
		                	failure: function(form, action) {
		                		switch (action.failureType) {
		                			case Ext.form.Action.CLIENT_INVALID:
		                				Ext.Msg.alert("Fehler", "Bitte überprüfen Sie Ihre Eingaben");
		                				break;
		                			case Ext.form.Action.CONNECT_FAILURE:
		                				Ext.Msg.alert("Fehler", "Ein unbekannter Fehler ist aufgetreten");
		                				break;
		                			case Ext.form.Action.SERVER_INVALID:
		                			default:
		                				Ext.Msg.alert("Fehler", action.result.message);
		                				break;
		                		}
		                	}
		                });
		            }
		        }]
			});
			
			this.download = new Ext.FormPanel({
			    title: 'Plugin herunterladen',
			    id: 'plugin_download_form',
			    defaults: { anchor: '100%', xtype:'textfield' },
				layout:'form',
		        labelWidth: 300, 
		        bodyStyle:'padding:20px',
			    items: [{
		            fieldLabel: 'Link',
		            allowBlank: false,
		            name: 'link',
		            buttonText: '',
		            buttonCfg: {
		                iconCls: 'upload-icon'
		            }
		        }],
		        buttonAlign:'right',
		        buttons: [{
		            text: 'Start',
		            handler: function(){
		            	var form = Ext.getCmp('plugin_download_form').getForm();
		                if(!form.isValid()) return;
		                Ext.MessageBox.wait("","Bitte warten ..."); 
		                form.submit({
		                	url: '{url action="download"}',
		                	success: function(fp, o){
		                		Ext.MessageBox.alert("Download erfolgreich!", ""); 
		                		Ext.getCmp('plugin_delete_field').store.load();
		                	},
		                	failure: function(form, action) {
		                		switch (action.failureType) {
		                			case Ext.form.Action.CLIENT_INVALID:
		                				Ext.Msg.alert("Fehler", "Bitte überprüfen Sie Ihre Eingaben");
		                				break;
		                			case Ext.form.Action.CONNECT_FAILURE:
		                				Ext.Msg.alert("Fehler", "Ein unbekannter Fehler ist aufgetreten");
		                				break;
		                			case Ext.form.Action.SERVER_INVALID:
		                			default:
		                				Ext.Msg.alert("Fehler", action.result.message);
		                				break;
		                		}
		                	}
		                });
		            }
		        }]
			});
			
			this.remove = new Ext.FormPanel({
			    title: 'Plugin löschen',
			    id: 'plugin_delete_form',
			    defaults: { anchor: '100%', xtype:'textfield' },
				layout:'form',
		        labelWidth: 300,
		        bodyStyle:'padding:20px',
			    items: [{
			    	id: 'plugin_delete_field',
		            fieldLabel: 'Plugin',
                    name:'path',
                    xtype: 'combo',
                    hiddenName:'path',
                    store:  new Ext.data.Store({
						url: '{url action="getDeleteList"}',
						autoLoad: true,
					   	reader: new Ext.data.JsonReader({
					    	root: 'data',
					        totalProperty: 'count',
					        id: 'path',
					        fields: ['path','name']
					    })
		            }),
                    valueField:'path',
                    displayField:'name',
                    mode: 'remote',
                    editable:false,
                    selectOnFocus:true,
                    triggerAction:'all',
                    forceSelection : true,
                    listeners: {
                        'blur': { fn:function(el){
                        	if(!el.getRawValue())
                        		el.setValue(null);
                        } }
                    }
		        }],
		        buttonAlign:'right',
		        buttons: [{
		            text: 'Start',
		            handler: function(){
		            	var form = Ext.getCmp('plugin_delete_form').getForm();
		                if(!form.isValid()) return;
		                Ext.MessageBox.wait("","Bitte warten ..."); 
		                form.submit({
		                	url: '{url action="delete"}',
		                	success: function(fp, o){
		                		Ext.MessageBox.alert("Löschen erfolgreich!", ""); 
		                		Ext.getCmp('plugin_delete_field').store.load();
		                		Ext.getCmp('plugin_delete_field').setValue(null);
		                	},
		                	failure: function(form, action) {
		                		switch (action.failureType) {
		                			case Ext.form.Action.CLIENT_INVALID:
		                				Ext.Msg.alert("Fehler", "Bitte überprüfen Sie Ihre Eingaben");
		                				break;
		                			case Ext.form.Action.CONNECT_FAILURE:
		                				Ext.Msg.alert("Fehler", "Ein unbekannter Fehler ist aufgetreten");
		                				break;
		                			case Ext.form.Action.SERVER_INVALID:
		                			default:
		                				Ext.Msg.alert("Fehler", action.result.message);
		                				break;
		                		}
		                	}
		                });
		            }
		        }]
			});
	    	
	    	this.items = [this.upload, this.download, this.remove];
	    	
	        Upload.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.Upload = Upload;
})();
</script>