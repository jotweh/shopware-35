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

        Ext.regModel('Referrers',{
			fields: [
				// set up the fields mapping into the xml doc
				// The first needs mapping, the others are very basic
				'count','referrer','referrerOriginal'
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'Referrers',
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url controller=WidgetDataStore action=getReferrer}',
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
                text   : 'Anzahl',
                width: 40,
                dataIndex: 'count'
            },{
                text   : 'Referrer',
                flex	:   1,
                sortable : true,
                dataIndex: 'referrer'
            },
			{
				xtype:'actioncolumn',
				width:25,
				items: [{
					icon: '{link file="backend/plugins/widgets/_resources/world_link.png"}',  // Use a URL in the icon config
					tooltip: 'Edit',
					handler: function(grid, rowIndex, colIndex) {
						var rec = grid.getStore().getAt(rowIndex).get('referrerOriginal');
						window.open(rec);
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