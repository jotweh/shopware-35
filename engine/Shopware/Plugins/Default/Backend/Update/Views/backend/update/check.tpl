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