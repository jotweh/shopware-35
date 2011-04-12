<script type="text/javascript">
Ext.ns('Shopware');
Shopware.Update = function(){
	var requestUrl = '{url controller=update action=checkVersion}';
	var createMessage =  function(version){
		if(version) {
			return Ext.Msg.confirm(
				'Update',
				'Das Update auf die Version '+version+' ist verfügbar. <br /> Wollen Sie jetzt das Update durchführen?',
				function(btn, text) {
					if (btn == 'yes'){
						openAction('update');
					}
				}
			);
		} else {
			return Ext.Msg.alert(
				'Update',
				' Kein Update gefunden. Sie haben schon die aktuellste Version installiert.'
			);
		}
	};
	var requestHandler = function(response, options){
		if(response.responseText) {
			response = Ext.decode(response.responseText);
		} else {
			response = null;
		}
		if(response) {
			createMessage(response.version);
		}
	};
	return {
		checkVersion : function(){
			Ext.Ajax.request({
				url: requestUrl,
				success: requestHandler
			});
		}
	};
}();
</script>
{if $VersionConfig}
<script type="text/javascript">
Ext.ns('Shopware.Update');
VersionInfo = Ext.extend(Ext.Panel, {
	title: 'Shopware Version {$VersionConfig->version|escape:javascript}',
	initComponent: function() {
		this.buttons = [{
			text: 'Update starten',
			handler: function() {
				openAction('update');
			}
		}];
		VersionInfo.superclass.initComponent.call(this);
	},
	bodyStyle :'padding:5px;font-family:Arial,Verdana,Helvetica,sans-serif;'
			  +'font-size:12px;font-size-adjust:none;',
	html: '{$VersionConfig->info|escape:javascript}',
	preventBodyReset: true
});
Shopware.Update.VersionInfo = VersionInfo;
</script>
<script type="text/javascript">
Ext.onReady(function(){
	var EastPanel = Ext.getCmp('accountpanel') ? Ext.getCmp('accountpanel').ownerCt : null;
	var VersionInfo;
	if(EastPanel) {
		VersionInfo = new Shopware.Update.VersionInfo();
		EastPanel.insert(2, VersionInfo);
		EastPanel.doLayout();
	}
});
</script>
{/if}