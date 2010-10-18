<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var Viewport = Ext.extend(Ext.Viewport, {
	    layout: 'border',
	    initComponent: function() {
	    	this.list = new Shopware.Plugin.List;
	    	this.upload = new Shopware.Plugin.Upload;
	    	
	    	this.tree = new Ext.tree.TreePanel({
	    		title: '&nbsp;{*s name="tree_titel"}Plugins{/s*}',
	    		width: 248,
	    		region: 'west',
	    		rootVisible:false,
	    		root: {
	    			id: '0'
	    		},
	    		loader: {
	    			url: '{url action="getTree"}'
	    		},
                listeners: {
                    'click': { scope:this, fn:function(el){
                    	this.list.store.baseParams["search"] = '';
                    	this.list.store.baseParams["path"] = el.id;
                    	this.list.store.load({ params:{ start:0, limit:20 } });
                    } }
                }
	    	});
	    	
	    	this.tabpanel = new Ext.TabPanel({
	    		activeTab: 0,
	    		region: 'center',
	    		enableTabScroll: true,
	    		items: [
		    		this.list, this.upload
	    		]
	    	});
	        this.items = [
	        	this.tree,
	        	this.tabpanel
	        ];
		    this.showDetail = function(pluginId) {
		    	$.ajax({
		    		url: '{url action="detail"}',
		    		context: this,
		    		data: { id: pluginId },
		    		dataType: 'jsonp',
		    		success: function(tab) {
		    			this.tabpanel.remove(tab.id);
		    			this.tabpanel.add(tab);
		    			this.tabpanel.activate(tab.id);
		    		}
		    	});
		    };
		    this.refreshList = function() {
		    	this.list.store.load();
		    };
		    this.installPlugin = function(pluginId, install) {
		    	if(install) {
					var message = 'Wollen Sie dieses Plugin wirklich installieren?';
					var url = '{url action="install"}';
				} else {
					var message = 'Wollen Sie dieses Plugin deinstallieren?';
					var url = '{url action="uninstall"}';
				}
				var Viewport = this; 
				Ext.MessageBox.confirm('', message, function(r){
					if(r!='yes') {
						return;
					}
					$.ajax({
			    		url: url,
			    		method: 'post',
			    		context: this, 
			    		data: { id: pluginId },
			    		dataType: 'json',
			    		success: function(result) {
			    			if(result.success && install) {
		   						var message = 'Das Plugin wurde erfolgreich installiert.';
		   					} else if (install) {
		   						var message = 'Das Plugin konnte nicht installiert werden';
		   					} else if(result.success) {
		   						var message = 'Das Plugin wurde erfolgreich deinstalliert.';
		   					} else {
		   						var message = 'Das Plugin konnte nicht deinstalliert werden';
		   					}
		   					Ext.MessageBox.alert('Plugin installieren/deinstallieren', message);
		   					Viewport.refreshList();
		   					if(result.success && install) {
		   						Viewport.showDetail(pluginId);
		   					}
			    		}
			    	});
				});
		    };
	        Viewport.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.Viewport = Viewport;
})();
</script>