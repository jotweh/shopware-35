{if 1 != 1}<script>{/if}
Ext.define('Swag.Widget.{$item}',
{
	extend: 'Ext.grid.Panel',
    height: 200,
    initComponent: function(){
		{if $widget.configuration.refresh}
					this.task = {
						run: this.refreshComponent,
						scope:this,
						interval: {$widget.configuration.refresh * 1000}
					};
					Ext.TaskManager.start(this.task);
		{/if}

        Ext.regModel('Edits',{
			fields: [
				// set up the fields mapping into the xml doc
				// The first needs mapping, the others are very basic
				'id','changetime', 'name'
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'Edits',
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=WidgetDataStore action=getLastEdits}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'result',
					totalRecords: 'total'
				}
			}
		});

        Ext.apply(this, {
            //height: 300,
            height: this.height,
            store: this.store,
            stripeRows: true,
            columnLines: true,
            columns: [{
                text   : 'Datum',
                width: 150,
                dataIndex: 'changetime'
            },{
                text   : 'Artikel',
                flex   : 1,
                dataIndex: 'name'
            },
			{
				xtype:'actioncolumn',
				text   : 'Optionen',
				width:50,
				items: [{
					icon: '{link file="backend/plugins/widgets/_resources/pencil.png"}',  // Use a URL in the icon config
					tooltip: 'Edit',
					handler: function(grid, rowIndex, colIndex) {
						var rec = grid.getStore().getAt(rowIndex);
						parent.loadSkeleton('articles',false,{ article: rec.get('id')});
						//parent.loadSkeleton(\'articles\',false, { \'article\':'+id+'});
					},
					style: 'cursor:pointer'
				}]
			}
			]
        });

        this.callParent(arguments);
    },
	refreshComponent: function(){
		this.store.load();
	}
}
);