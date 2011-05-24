<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
	var List = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Verfügbare Plugins',
	    stripeRows:true,
	    initComponent: function() {
	    	Ext.QuickTips.init();
	    	var selModel = this.selModel = new Ext.grid.RowSelectionModel({ singleSelect: true });
	    	
	    	this.store = new Ext.data.Store({
	   			url: '{url action=getList}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'id',
	   				fields: [
	   					'id', 'path', 'namespace', 'name', 'autor', 'version', 'active','added', 'copyright', 'license', 'label', 'source', 'support', 'link',
	   					{ name: 'update_date', type: 'date', dateFormat: 'timestamp' },
	        			{ name: 'installation_date', type: 'date', dateFormat: 'timestamp' }
	   				]
	   			})
	    	});

			function nl2br (str, is_xhtml) {
				 var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br />';
   				 return (str + '').replace(/(\n)/g, '$1' + breakTag)
			}
			
			this.store.on('exception',function (misc,type,action,options,response){
				var text = nl2br(response.responseText,true);
				var code = response.status;
				var info = "Bitte beheben Sie den Fehler oder löschen Sie das fehlerhafte Plugin!";
				Ext.Msg.show({
				   title:'Fehler! Plugin-Liste konnte wegen eines defekten Plugins nicht geladen werden',
				   msg: '<strong>Fehler-Protokoll: </strong><br />'+text+info,
				   buttons: Ext.Msg.OK,
				   animEl: 'elId',
				   icon: Ext.MessageBox.ERROR,
				   maxWidth: 700,
				   minWidth: 700
				});
			});
	    	
	    	this.getView().getRowClass = function(record, index) {
	    		if (!record.data.active) {
	    			return 'inactive';
	    		}
	    	};

			this.on('rowdblclick', function(grid,rowIndex,e){
				if(!rowIndex) rowIndex = '0';
				var rec = grid.getStore().getAt(rowIndex);
				Viewport.showDetail(rec.get('id'));
			});

			this.tbar = new Ext.Toolbar(
				{
				items:
				[
					new Ext.Button(
						{
							text: 'Nach Updates suchen'
						}
					),
					'-',
					new Ext.Button(
						{
							text: 'Temp'
						}
					)
				]
				}
			);
	    	this.bbar = new Ext.PagingToolbar({
	    		pageSize: 25,
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
		    					this.store.load({ params:{ start:0, limit:25 } });
		    				}, this, { buffer:500 });
		    			}, scope:this }
		    			}
		    		}
	    		]
	    	});

   			
	        this.columns = [
	            {
	                xtype: 'gridcolumn',
	                header: '&nbsp;',
	                sortable: false,
	                width: 75,
	                renderer: function (value, p, record) {
	                	if(!record.data.installation_date) {
	                		var r = '<a class="ico add" onclick="Viewport.installPlugin('+record.data.id+', true);"></a>';
	                	} else {
							var r = '<a class="ico cog" onclick="Viewport.showDetail('+record.data.id+');"></a>';
	                		r += '<a class="ico delete" onclick="Viewport.installPlugin('+record.data.id+', false);"></a>';
	                	}

	                	return r;
	                }
	            },
	         	{
	                xtype: 'gridcolumn',
	                dataIndex: 'label',
	                header: 'Name',
	                sortable: false,
	                width: 150,
	                renderer: function (v,p,r){
						p.attr = 'ext:qtip="Installationsdatum:'+Ext.util.Format.date(r.data.installation_date,'d.m.Y')+'<br />Lizenz: '+r.data.license+'" ext:qtitle="'+r.data.label+'"';
	                	return "<span style=\"font-weight:bold\">"+v+"</span";
	                }
	            },
				{
	                xtype: 'gridcolumn',
	                dataIndex: 'added',
	                header: 'Hinzugefügt',
	                sortable: false,
	                width: 200,
	                renderer: function (v,p,r){
						if (v == "0000-00-00 00:00:00") return "";
	                	return "<span style=\"font-weight:bold\">"+v+"</span";
	                }
	            },
	        	{
	                xtype: 'gridcolumn',
	                dataIndex: 'path',
	                header: 'Pfad',
	                sortable: false,
	                width: 200
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
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'version',
	                header: 'Ihre Version',
	                sortable: false,
	                width: 95,
					editable: false,
					align: 'left',
	                renderer: function (v,p,r){
						if (r.data.source == "Default"){
							return "Shopware 3.5.4";
						}
						return v;
					}
	            },
				{
	                xtype: 'gridcolumn',
	                dataIndex: 'version',
	                header: 'Aktuelle Version',
	                sortable: false,
	                width: 95,
					editable: false,
					align: 'left',
	                renderer: function (v,p,r){
						if (r.data.source != "Community"){
							return "-";
						}
						return '?';
					}
	            },
				{
	                xtype: 'gridcolumn',
	                dataIndex: 'autor',
	                header: 'Hersteller',
	                sortable: false,
	                width: 100,
	                renderer: function (value, p, record){
	                	if(!record.data.link) {
	                		return record.data.autor;
	                	}
	                	return '<a h'+'ref="'+record.data.link+'" target="_blank">'+record.data.autor+'</a>';
	                }
	            },
	        ];
	        List.superclass.initComponent.call(this);
	    }
	});
	Shopware.Plugin.List = List;
})();
</script>