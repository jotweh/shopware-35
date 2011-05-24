<script type="text/javascript">
Info = Ext.extend(Ext.FormPanel, {
	title: 'Update-Informationen',
	closable: false,
	defaults: { anchor: '100%', xtype:'textfield' },
	layout: 'form',
    labelWidth: 300,
    bodyStyle: 'padding:20px',
	autoScroll: true,
	initComponent: function() {
		this.fieldsetBase = {
            xtype:'fieldset',
            title: 'Allgemeine Informationen',
            autoHeight: true,
            defaults: { anchor: '100%' },
            defaultType: 'textfield',
            items :[{
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				bodyStyle:'font-family:Arial,Verdana,Helvetica,sans-serif;'
						 +'font-size:12px;font-size-adjust:none;/*font-weight:bold;*/',
				html: '{$VersionConfig->info|escape:javascript}<br /><br />',
				preventBodyReset: true
			},{
				fieldLabel: 'Ihre Version',
				name: 'version',
				value: '{config name=Version}',
				readOnly: true,
				disabled: true
			},{
				fieldLabel: 'Neue Version',
				name: 'version',
				value: '{$VersionConfig->version}',
				readOnly: true,
				disabled: true
			}]
		};
		this.fieldsetChanges = {
            xtype:'fieldset',
            title: 'Änderungen',
            autoHeight: true,
            defaults: { anchor: '100%' },
            defaultType: 'textfield',
            items :[{
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				bodyStyle:'font-family:Arial,Verdana,Helvetica,sans-serif;'
						 +'font-size:12px;font-size-adjust:none;',
				html: '{$VersionConfig->changes|utf8_decode|escape:javascript}',
				preventBodyReset: true
			}]
		};
		this.items = [this.fieldsetBase, this.fieldsetChanges];
		this.buttons = [{
	        text: 'Weiter',
	        handler: function(){
	        	Update.Tabs.activate(Update.ConfigForm);
	        },
	        scope: this
	    }];
        Info.superclass.initComponent.call(this);
	}
});
Shopware.Update.Info = Info;
</script>