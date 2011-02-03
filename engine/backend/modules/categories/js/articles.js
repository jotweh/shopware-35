
	var articlestore = new Ext.data.Store({
		url: 'ajax/getArticles.php',
		//autoLoad: true,
		remoteSort: true,
		reader: new Ext.data.JsonReader({
			root: 'articles',
			totalProperty: 'count',
			id: 'id',
			fields: [
				'id', 'ordernumber', 'name', 'supplier'
			]
		})
	});
	
	var cols = [
		{id:'ordernumber', dataIndex: 'ordernumber', header: "Artikelnummer", width: 200, sortable: true},
		{id:'name', dataIndex: 'name', header: "Artikelname", width: 200, sortable: true},
		{id:'supplier', dataIndex: 'supplier', header: "Hersteller", width: 200, sortable: true},
		{id:'options', dataIndex: 'options', header: "Optionen", width: 100, sortable: true, renderer: function (value, p, r){
			return String.format(
				'<a class="ico pencil_arrow" style="cursor:pointer" onclick="parent.loadSkeleton({2},false,{3})"></a>',
				r.data.id,
				"'"+r.data.lastname+"'",
				"'articles'",
				"{'article':"+r.data.id+"}"
			);
		}}
	];
	
	var articlegrid = new Ext.grid.GridPanel({
		id: 'articlegrid',
		title: 'Verfügbare Artikel',
		closable: false,
		store: articlestore,
		region:'west',
		margins: '5 0 5 0',
		width: '50%',
		minSize: 100,
		ddGroup: 'secondGridDDGroup',
		enableDragDrop: true,
		stripeRows: true,
		columns: cols,
		//viewConfig: { forceFit:true },
		bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: articlestore,
            displayInfo: true,
            displayMsg: 'Zeige Eintrag {0} bis {1} von {2}',
            items:[
                '-', 'Suche: ',
	            {
	            	xtype: 'textfield',
	            	id: 'articlesearch',
	            	selectOnFocus: true,
	            	width: 120,
	            	listeners: {
		            	'render': {fn:function(ob){
		            		ob.el.on('keyup', function(){
		            			var search = Ext.getCmp("articlesearch");
							    articlestore.baseParams["search"] = search.getValue();
							    articlestore.load({params:{start:0, limit:25}});
		            		}, this, {buffer:500});
		            	}, scope:this}
	            	}
	            }
            ]
        }),
		listeners: {
			'rowdblclick': {fn:function(grid, rowIndex, e){
				var record = Ext.getCmp("articlegrid").store.getAt(rowIndex);
				Ext.getCmp("articlegrid").store.remove(record);
				Ext.getCmp("articlegrid2").store.add(record);
			}, scope:this}
		}
	});
	//articlestore.load({params:{start:0, limit:25}});
	
	var articlestore2 = new Ext.data.Store({
		url: 'ajax/getArticles.php',
		//autoLoad: true,
		remoteSort: true,
		baseParams: {invert: 1},
		reader: new Ext.data.JsonReader({
			root: 'articles',
			totalProperty: 'count',
			id: 'id',
			fields: [
				'id', 'ordernumber', 'name', 'supplier'
			]
		})
	});
	var articlegrid2 = new Ext.grid.GridPanel({
		id:'articlegrid2',
		title: 'Ausgewählte Artikel',
		closable:false,
		store: articlestore2,
		region:'center',
		margins: '5 0 5 0',
		minSize: 100,
		ddGroup: 'firstGridDDGroup',
		enableDragDrop: true,
		stripeRows: true,
		columns: cols,
		//viewConfig: { forceFit:true },
		bbar: new Ext.PagingToolbar({
            pageSize: 25,
            store: articlestore2,
            displayInfo: true,
            displayMsg: 'Zeige Eintrag {0} bis {1} von {2}',
            items:[
                '-', 'Suche: ',
	            {
	            	xtype: 'textfield',
	            	id: 'articlesearch2',
	            	selectOnFocus: true,
	            	width: 120,
	            	listeners: {
		            	'render': {fn:function(ob){
		            		ob.el.on('keyup', function(){
		            			var search = Ext.getCmp("articlesearch2");
							    articlestore2.baseParams["search"] = search.getValue();
							    articlestore2.load({params:{start:0, limit:25}});
		            		}, this, {buffer:500});
		            	}, scope:this}
	            	}
	            }
            ]
        }),
		listeners: {
			'rowdblclick': {fn:function(grid, rowIndex, e){
				var record = Ext.getCmp("articlegrid2").store.getAt(rowIndex);
				Ext.getCmp("articlegrid2").store.remove(record);
				Ext.getCmp("articlegrid").store.add(record);
			}, scope:this}
		}
	});
	//articlestore2.load({params:{start:0, limit:25}});
	
	var articles = {
   		title:'Artikel-Auswahl',
   		layout:'border',
   		id: 'articles',
   		disabled : true,
		defaults: {
		    collapsible: false,
		    split: true
		},
		items: [articlegrid, articlegrid2],
   		buttonAlign:'right',
        buttons: [{
            text: 'Speichern',
            handler: function(){
            	var categoryID = articlestore2.baseParams.categoryID;
            	var articleIDs = [];
            	articlestore2.each(function(record, i){
					articleIDs[i] = record.data.id;
				});
				
				var delete_articleIDs = [];
            	articlestore.each(function(record, i){
					delete_articleIDs[i] = record.data.id;
				});
				
				new Request({method: 'post', url: 'ajax/saveArticles.php', async: false, data: {'delete_articleIDs[]': delete_articleIDs, 'articleIDs[]': articleIDs, categoryID: categoryID}}).send();
				Ext.getCmp('articlegrid').store.load();
				Ext.getCmp('articlegrid2').store.load();
	        }
        }]
   	}