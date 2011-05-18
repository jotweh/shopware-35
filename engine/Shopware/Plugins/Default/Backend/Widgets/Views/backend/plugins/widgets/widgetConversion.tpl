{if 1 != 1}<script>{/if}
Ext.define('Swag.Widget.{$item}',
{
	extend: 'Ext.panel.Panel',
	    initComponent: function(){
			Ext.apply(this, {
				layout: 'fit',
				height: 150
			});


			{if $widget.configuration.refresh}
				this.task = {
					run: this.refreshComponent,
					scope:this,
					interval: {$widget.configuration.refresh * 1000}
				};
				Ext.TaskManager.start(this.task);
			{/if}

			
			Ext.core.DomHelper.append(Ext.getHead(), { tag: 'link', href: '{url controller=Widgets action=getStyleSheet css=widgetConversion widget=$widgetType}',rel: 'stylesheet',type: 'text/css'});



			this.status = new Ext.panel.Panel(
			{
				{literal}
				tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'<div class="triangle-widget"><div class="triangle-info">{abs}</div>',
				'<div class="triangle-{updown}"></div>',
				'<div class="triangle-data">{percent} %</div></div>',
				'<div class="triangle-legend">Start bis {datePoint}: {historical} % <br />{datePoint} bis jetzt: {current} %</div>',
				'</tpl>'
				)
				{/literal}
			});


			this.items =
			{
				xtype: 'container',
				layout: 'fit',
				items: [this.status]
			};
			this.refreshComponent();

			this.callParent(arguments);
		},
		refreshComponent: function(){
			var url = this.dataProvider;
			Ext.Ajax.request({
				url:  '{url controller=WidgetDataStore action=getConversion id=$widgetUid}',
				method: 'post',
				scope:this,
				success: function(response){
					var text = Ext.JSON.decode(response.responseText);
					this.status.update(this.status.tpl.apply({ abs: text.abs,updown:text.updown,percent:text.percent,datePoint: text.datePoint,historical: text.historical,current: text.current}));
					this.status.body.highlight('#ED9200', { block:true});
					//this.html = this.tpl.apply({ state: text.state});
					//console.log(this.html);
					//this.doLayout();
					// process server response here
				}
			});
		}
}
);