{if 1 != 1}<script>{/if}
Ext.define('Swag.Widget.{$item}',
{
	extend: 'Ext.panel.Panel',
	initComponent: function(){
		{if $widget.configuration.refresh}
			this.task = {
				run: this.refreshComponent,
				scope:this,
				interval: {$widget.configuration.refresh * 1000}
			};
			Ext.TaskManager.start(this.task);
		{/if}

		
		Ext.regModel('Amount',{
			fields: [
				// set up the fields mapping into the xml doc
				// The first needs mapping, the others are very basic
				'day','amount','count'
			]
		});
		// create the Data Store
		this.store = Ext.create('Ext.data.Store', {
			autoLoad: true,
			proxy: {
				// load using HTTP
				type: 'ajax',
				model: 'Amount',
				url: '{url controller=WidgetDataStore action=getAmount id=$widgetUid}',
				// the return will be XML, so lets set up a reader
				reader: {
					type: 'json',
					root: 'data'
				}
			}
		});
		this.store.load();
		
		Ext.apply(this, {
					layout: 'fit',
					width: 550,
					height: 250,
					items: {
						xtype: 'chart',
						animate: true,
						width: 450,
						padding: '5 5 5 5',
						store: this.store,
						shadow: true,
						theme: 'Category1',
						legend: {
							position: 'right',
							labelFont: '12px Arial'
						},
						axes: [{
							type: 'Numeric',
							minimum: 0,
							position: 'left',
							title: ' ',
							fields: ['amount','count'],
							label: {
								  display: 'insideEnd',
								  'text-anchor': 'middle',
								orientation: 'horizontal',
								font: '12px Arial'
							}
						}, {
							type: 'Category',
							position: 'bottom',
							fields: ['day'],
							title: 'Day',
							label: {
								  display: 'insideEnd',
								  'text-anchor': 'middle',
								orientation: 'horizontal',
								rotate: {
									degrees: 315
								},
								font: '12px Arial'
							}
						}],
						series: [
						{
							type: 'line',
							highlight: {
								size: 7,
								radius: 7
							},
							axis: 'left',
							xField: 'day',
							smooth: true,
							fill: true,
							yField: 'amount',
							markerCfg: {
								type: 'cross',
								size: 4,
								radius: 4,
								'stroke-width': 0
							},
							tips: {
								trackMouse: true,
								width: 80,
								height: 40,
								renderer: function(storeItem, item) {
									this.setTitle(storeItem.get('day') + '<br />' + storeItem.get('amount'));
								}
							}
						},
						{
							type: 'line',
							axis: 'left',
							xField: 'day',
							smooth: false,
							fill: false,
							yField: 'count',
							tips: {
								trackMouse: true,
								width: 80,
								height: 40,
								renderer: function(storeItem, item) {
									this.setTitle(storeItem.get('day') + '<br />' + storeItem.get('count'));
								}
							}
						}
						]
					}
				});

		this.callParent(arguments);
	},
	refreshComponent: function(){
		this.store.load();
	}
}
);