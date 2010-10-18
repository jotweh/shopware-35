<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var List = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Übersicht',
	    initComponent: function() {
	    	
	    	var selModel = this.selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
	    	
	    	this.store = new Ext.data.Store({
	   			url: '{url action=getList}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'id',
	   				fields: [
	   					'id', 'path', 'namespace', 'name', 'autor', 'version', 'active', 'copyright', 'license', 'label', 'source', 'support', 'link',
	   					{ name: 'update_date', type: 'date', dateFormat: 'timestamp' },
	        			{ name: 'installation_date', type: 'date', dateFormat: 'timestamp' }
	   				]
	   			})
	    	});
	    	
	    	this.getView().getRowClass = function(record, index) {
	    		if (!record.data.active) {
	    			return 'inactive';
	    		}
	    	};

	    	this.bbar = new Ext.PagingToolbar({
	    		pageSize: 20,
	    		store: this.store,
	    		displayInfo: true,
	    		items:[
		    		'-', 'Suche: ',
		    		{
		    			xtype: 'textfield',
		    			id: 'usersearch',
		    			selectOnFocus: true,
		    			width: 120,
		    			listeners: {
		    			'render': { fn:function(ob){
		    				ob.el.on('keyup', function(){
		    					var search = Ext.getCmp("usersearch");
		    					this.store.baseParams["path"] = '';
		    					this.store.baseParams["search"] = search.getValue();
		    					this.store.load({ params:{ start:0, limit:20 } });
		    				}, this, { buffer:500 });
		    			}, scope:this }
		    			}
		    		}
	    		]
	    	});
			/*
	    	this.tbar = [
	    	{
	    		text:'Plugin editieren',
	   				iconCls:'pencil',
	   				handler: function (el, el2){
	   					if(!selModel.getSelected()) {
	   						return;
	   					}
	   					var pluginId = selModel.getSelected().id;
	   					
	   					Viewport.showDetail(pluginId);
	
	   				}
	   			},'-',{
	   				text:'Plugin aktvieren/deaktivieren',
	   				iconCls:'add',
	   				handler: function (){
	
	   				},
	   			},'-',{
	   				text: 'Plugin installieren/deinstallieren',
	   				iconCls:'folders_plus',
	   				handler: function (a, b, c) {
	   					var pluginId = selModel.getSelected().id;
	   					var install = !selModel.getSelected().data.installation_date;

	   					Viewport.installPlugin(pluginId, install);
	   				}
	   			},'-',{
	   				text: 'Plugin aktualisieren',
	   				iconCls:'refresh',
	   				disabled: true,
	   				handler: function (a, b, c){
	   					Ext.MessageBox.confirm('', 'Wollen Sie wirklich dieses Plugin aktualisieren?', function(r){
	   						if(r=='yes') {
	   							
	   						}
	   					});
	   				}
	   			}
   			];
			*/
   			
	        this.columns = [
	        	{
	                xtype: 'gridcolumn',
	                dataIndex: 'path',
	                header: 'Pfad',
	                sortable: false,
	                width: 200
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'label',
	                header: 'Name',
	                sortable: false,
	                width: 150
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'autor',
	                header: 'Autor',
	                sortable: false,
	                width: 100,
	                renderer: function (value, p, record){
	                	if(!record.data.link) {
	                		return record.data.autor;
	                	}
	                	return '<a h'+'ref="'+record.data.link+'" target="_blank">'+record.data.autor+'</a>';
	                }
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'license',
	                header: 'Lizenz',
	                sortable: false,
	                width: 100
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'version',
	                header: 'Version',
	                sortable: false,
	                width: 75,
					editable: false,
					align: 'right'
	            },
	            {
	                xtype: 'booleancolumn',
	                dataIndex: 'active',
	                header: 'Aktiv',
	                sortable: false,
	                width: 75,
	                trueText: 'ja',
	                falseText: 'nein'
	            },
	            /*{
	                xtype: 'gridcolumn',
	                dataIndex: 'copyright',
	                header: 'Copyright',
	                sortable: true,
	                width: 100
	            },*/
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'support',
	                header: 'Hilfe',
	                sortable: false,
	                width: 75,
	                renderer: function (value, p, record){
	                	if(record.data.support.indexOf('http')!==0) {
	                		return record.data.support;
	                	}
	                	return '<a h'+'ref="'+record.data.support+'" target="_blank">[link]</a>';
	                }
	            },
	            { header: "Installationsdatum", width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), dataIndex: 'installation_date' },
	            { header: "Updatedatum", width: 150, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), dataIndex: 'Installationsdatum' },
	            {
	                xtype: 'gridcolumn', 
	                header: '&nbsp;',
	                sortable: false,
	                width: 75,
	                renderer: function (value, p, record) {
	                	
						var r = '<a class="ico pencil" onclick="Viewport.showDetail('+record.data.id+');"></a>';
						
	                	if(!record.data.installation_date) {
	                		r += '<a class="ico add" onclick="Viewport.installPlugin('+record.data.id+', true);"></a>'
	                	} else {
	                		r += '<a class="ico delete" onclick="Viewport.installPlugin('+record.data.id+', false);"></a>'
	                	}
	                		                	
	                	return r;
	                }
	            }
	        ];
	        List.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.List = List;
})();
</script>