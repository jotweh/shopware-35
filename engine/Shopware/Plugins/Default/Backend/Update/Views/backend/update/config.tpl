<script type="text/javascript">
Config = Ext.extend(Ext.FormPanel, {
	title: 'Update-Einstellungen',
	closable: false,
	defaults: { anchor: '100%', xtype:'textfield' },
	layout:'form',
    labelWidth: 120,
    bodyStyle:'padding:20px',
	autoScroll: true,
	initComponent: function() {
		
		this.fieldsetBase = {
            xtype:'fieldset',
            title: 'Allgemeine Einstellungen',
            autoHeight:true,
            defaults: { anchor: '100%' },
            defaultType: 'textfield',
            items :[{
				fieldLabel: 'Paket',
				name: 'package',
				hiddenName:'package',
				valueField:'id',
				displayField:'name',
				triggerAction:'all',
				xtype: 'combo',
				allowBlank:false,
				value: 'Auto',
				mode: 'remote',
				emptyText:'Bitte wählen...',
                selectOnFocus:true,
                forceSelection : true,
				store:  new Ext.data.Store({
					url: '?action=packageList',
					autoLoad: true,
					reader: new Ext.data.JsonReader({
						root: 'data',
						totalProperty: 'count',
						id: 'id',
						fields: ['id', 'name']
					})
				})
			},{
				fieldLabel: 'Methode',
				name: 'method',
				hiddenName:'method',
				valueField:'id',
				displayField:'name',
				triggerAction:'all',
				xtype: 'combo',
				allowBlank:false,
				readOnly: true,
				value: 'Auto',
				mode: 'remote',
				emptyText:'Bitte wählen...',
                selectOnFocus:true,
                forceSelection : true,
				store:  new Ext.data.Store({
					url: '?action=methodList',
					autoLoad: true,
					reader: new Ext.data.JsonReader({
						root: 'data',
						totalProperty: 'count',
						id: 'id',
						fields: ['id', 'name']
					})
				})
			},{
				fieldLabel: 'Format',
				name: 'format',
				hiddenName:'format',
				valueField:'id',
				displayField:'name',
				triggerAction:'all',
				xtype: 'combo',
				allowBlank:false,
				readOnly: true,
				value: 'Auto',
				mode: 'remote',
				emptyText:'Bitte wählen...',
                selectOnFocus:true,
                forceSelection : true,
				store:  new Ext.data.Store({
					url: '?action=formatList',
					autoLoad: true,
					reader: new Ext.data.JsonReader({
						root: 'data',
						totalProperty: 'count',
						id: 'id',
						fields: ['id', 'name']
					})
				})
			}]
		};
		
		this.fieldPath = {
			fieldLabel: 'Verzeichnis',
			name: 'ftp_path',
			id: 'ftp_path',
			xtype: 'combo',
			store: new Ext.data.SimpleStore({ fields:[["path"]], data:[[]] }),
			value: '',
			mode: 'local',
			tpl: '<tpl for="."></tpl>',
			selectedClass:'',
			onSelect:Ext.emptyFn,
			listeners: {
				'expand': { fn:function(combobox){
					var baseParams = this.getForm().getValues();
					
					Ext.destroy(Ext.getCmp('ftp_progress'));
					var progress = new Ext.ProgressBar({
						id: 'ftp_progress',
						text:'Bitte warten...',
						renderTo: combobox.innerList,
						style: 'margin:20px;',
						border:false
					}).wait({
						text:'Bitte warten...',
						interval:200,
						increment:15
					});
					Ext.destroy(Ext.getCmp('ftp_treepanel'));
					var tree = new Ext.tree.TreePanel({
						renderTo: combobox.innerList,
						id: 'ftp_treepanel',
						height: 200,
						loader: new Ext.tree.TreeLoader({
							dataUrl:'{url action=ftpPathList}',
							baseParams: baseParams
						}),
						hidden: true,
						border:false,
						root: new Ext.tree.AsyncTreeNode({ id:'.' }),
						rootVisible:false,
						autoScroll: true,
						containerScroll: true,
						listeners: {
							'click': { fn:function(node){
								combobox.store.add(new Ext.data.Record({ path: node.id }, node.id));
								combobox.setValue(node.id);
								combobox.collapse();
							}, scope:this},
							'load': { fn:function(node){
								if(!node.isRoot) {
									return;
								}
								if(progress) {
									progress.destroy();
								}
								if(node.childNodes.length) {
									Ext.getCmp('ftp_treepanel').show();
									combobox.restrictHeight();
								} else {
									Ext.destroy(Ext.getCmp('ftp_treepanel'));
									new Ext.Panel({
										renderTo: this.innerList,
										id: 'ftp_treepanel',
										bodyStyle:'padding:3px 5px;font-family:Arial,Verdana,Helvetica,sans-serif;'
										         +'font-size:12px;font-size-adjust:none;font-weight:bold;',
										html: 'Es konnte keine Verbindung zum Ftp-Server hergestellt werden.',
										border:false
									});
									combobox.restrictHeight();
								}
							}, scope:this}
						}
					});
				}, scope:this}
			}
		};
	
		this.fieldsetDb = {
	        xtype:'fieldset',
	        title: 'FTP-Einstellungen',
	        autoHeight:true,
	        defaults: { anchor: '100%' },
	        defaultType: 'textfield',
	        items :[{
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				bodyStyle:'padding:3px 0 12px;font-family:Arial,Verdana,Helvetica,sans-serif;'
						 +'font-size:12px;font-size-adjust:none;font-weight:bold;',
				html: 'Bitte geben Sie hier Ihre FTP-Zugangsdaten ein, die Sie von Ihren Provider erhalten haben.<br />'
			},{
				fieldLabel: 'Benutzer',
				name: 'ftp_user',
				allowBlank:false
			},{
				fieldLabel: 'Passwort',
				name: 'ftp_password',
				inputType: 'password'
			},{
				fieldLabel: 'Server',
				name: 'ftp_host',
				value: 'default',
				allowBlank:false
			},{
				fieldLabel: 'Port',
				name: 'ftp_port',
				value: 'default'
			},this.fieldPath]
		};
		
		this.items = [this.fieldsetBase, this.fieldsetDb];
		this.buttons = [{
	        text: 'Weiter',
	        handler: function(){
	        	var form = this.getForm();
	            if(!form.isValid()) {
	            	return;
	            }
	            Ext.MessageBox.wait("","Bitte warten ..."); 
	            form.submit({
	            	url: '{url action="testConfig"}',
	            	success: function(fp, o){
	            		Update.Tabs.activate(Update.HandlerFrom);
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
	        },
	        scope: this
	    }];
        Config.superclass.initComponent.call(this);
	}
});
Shopware.Update.Config = Config;
</script>