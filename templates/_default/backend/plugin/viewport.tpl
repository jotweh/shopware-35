<script type="text/javascript">
Ext.ns('Shopware.Plugin');
Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
 onRender : function(ct, position){
      this.el = ct.createChild({ tag: 'iframe', id: 'framepanel'+this.id, frameBorder: 0, src: this.url});
 }
});
(function(){
	var Viewport = Ext.extend(Ext.Viewport, {
	    layout: 'border',
	    initComponent: function() {
	    	this.list = new Shopware.Plugin.List;
	    	this.upload = new Shopware.Plugin.Upload;
			this.communityStore = new Ext.ux.IFrameComponent({ 
							title:'Shopware CommunityStore',
							autoScroll:true,
							id: "iframe", 
							height:600,
							width: 1000,
							url: 'http://store.shopware.de',
							tbar: [
								new Ext.Button  ({
					            	text: 'Store im neuen Fenster öffnen',
					            	handler: function(){
					            		
					            	},
					            	scope:this
				             	})
							]
			});
			
			this.store = new Ext.Panel(
			{
				autoScroll:true,
				title: 'Shopware CommunityStore',
				items: [
					this.communityStore
				],
				tbar: [
					new Ext.Button  ({
		            	text: 'Store im neuen Fenster öffnen',
		            	handler: function(){
		            		window.open("http://store.shopware.de/");
		            	},
		            	scope:this
	             	})
				]
			}
			);
			
	
	    	this.tree = new Ext.tree.TreePanel({
	    		title: 'Verzeichnisse',
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
		    		this.list, this.upload,this.store
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
						Ext.QuickTips.init();
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
					var message = 'Soll dieses Plugin installiert werden?';
					var url = '{url action="install"}';
				} else {
					var message = 'Soll dieses Plugin deinstalliert werden?';
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
								if (!result.message){
									result.message = "Install-Methode hat \"false\" zurückgegeben. Bitte kontaktieren Sie den Plugin-Hersteller!";
								}
								Ext.Msg.show({
								   title:'Plugin konnte nicht installiert werden!',
								   msg: '<strong>Fehler-Protokoll: </strong><br />'+result.message+ "<br />Aktivieren Sie ggf. die Optionen throwExceptions und showException in der Application.php um eindeutige Fehlermeldungen zu erhalten! ",
								   buttons: Ext.Msg.OK,
								   animEl: 'elId',
								   icon: Ext.MessageBox.ERROR,
								   maxWidth: 700,
								   minWidth: 700
								});
								return false;
		   					} else if(result.success) {
		   						var message = 'Das Plugin wurde erfolgreich deinstalliert.';
		   					} else {
								Ext.Msg.show({
								   title:'Plugin konnte nicht deinstalliert werden!',
								   msg: '<strong>Fehler-Protokoll: </strong><br />'+result.message,
								   buttons: Ext.Msg.OK,
								   animEl: 'elId',
								   icon: Ext.MessageBox.ERROR,
								   maxWidth: 700,
								   minWidth: 700
								});
								return false;
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