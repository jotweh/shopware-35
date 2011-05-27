<script type="text/javascript">
Ext.ns('Shopware.Plugin');
(function(){
var UpdateWindow = Ext.extend(Ext.Window, {
    title: 'Nach Updates suchen',
	width: 700,
	height: 400,
	layout: 'border',
	closeAction: 'hide',
	plain: true,
	resizable:false,
	autoScroll:false,
	modal:true,
    initComponent: function() {
		this.grid = new Ext.grid.GridPanel(
		{
			region: 'center',
			store: new Ext.data.Store({
	   			url: '{url action=getList}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				fields: [
	   					'id', 'path', 'namespace', 'name', 'autor', 'version', 'active','added', 'copyright', 'license', 'label', 'source', 'support', 'link',
	   					{ name: 'update_date', type: 'date', dateFormat: 'timestamp' },
	        			{ name: 'installation_date', type: 'date', dateFormat: 'timestamp' }
	   				]
	   			})
	    	}),
			title: 'Community Plugins',
			columns: [
	            {
	                xtype: 'gridcolumn',
	                header: 'Installation',
	                sortable: false,
	                width: 85,
					scope:this
	            }
			]
		}
		);
		this.items = this.grid;
	  UpdateWindow.superclass.initComponent.call(this);
	}
	});
	Shopware.Plugin.UpdateWindow = UpdateWindow;
})();
</script>