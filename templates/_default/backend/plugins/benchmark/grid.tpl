{if 1 != 1}<script>{/if}
Ext.define('Ext.app.Monitor.Grid',
{
	extend: 'Ext.grid.Panel',
    height: 200,
    initComponent: function(){
		//S executionDate, SUM(time) AS executionTime, COUNT(id) AS executionCount,query,parameters,route
        Ext.regModel('Bench',{
			fields: [
				'executionDate','executionTime', 'executionCount','query','parameters','route','executionMin','executionAvg','executionMax'
			]
	    });

		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			model: 'Bench',
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				url: '{url action=getQueries}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					// records will have an "Item" tag
					root: 'result',
					totalRecords: 'total'
				}
			}
		});
//'executionDate','executionTime', 'executionCount','query','parameters','route'
        Ext.apply(this, {
            store: this.store,
            stripeRows: true,
			region: 'center',
			// paging bar on the bottom
			bbar: Ext.create('Ext.PagingToolbar', {
				store: this.store,
				displayInfo: true,
				displayMsg: 'Displaying queries {0} - {1} of {2}',
				emptyMsg: "No queries to display",
				items:[]
			}),
            columnLines: true,
            columns: [{
                text   : 'Datum',
                width: 150,
                dataIndex: 'executionDate'
            },
			{
                text   : 'Zeit kumuliert',
  				width: 80,
                dataIndex: 'executionTime'
            },
			{
                text   : 'Zeit min.',
                width: 80,
                dataIndex: 'executionMin'
            },
			{
                text   : 'Zeit max.',
                width: 80,
                dataIndex: 'executionMax'
            },
			{
                text   : 'Zeit avg.',
                width: 80,
                dataIndex: 'executionAvg'
            },
			{
                text   : 'Anzahl',
                width: 80,
                dataIndex: 'executionCount'
            },
			{
                text   : 'Query',
                flex   : 1,
                dataIndex: 'query'
            },
			{
                text   : 'Parameter',
                flex   : 1,
                dataIndex: 'parameters'
            },
			{
                text   : 'Route',
                flex   : 1,
                dataIndex: 'route'
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
			],
			listeners: {
                selectionchange: function(model, records) {
                    if (records[0]) {
                        //this.up('form').getForm().loadRecord(records[0]);
						this.form.getForm().loadRecord(records[0]);
                    }
                }
            }
        });

        this.callParent(arguments);
    }
}
);