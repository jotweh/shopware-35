<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var Path = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Shopware-Verzeichnisse',
	    initComponent: function() {
	    	this.bbar = {
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				html: '<span class="info">Bitte setzten Sie bei diesen Ordnern die Dateirechte rekursiv auf 777, da Shopware ansonsten nicht ordnungsgem‰ﬂ lauff‰hig ist.</span>'
			};
	    	this.store = new Ext.data.Store({
	   			url: '?action=pathList',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'position',
	   				fields: [
	   					'name', 'compare_result', 'position'
	   				]
	   			})
	    	});
	    	this.viewConfig = {
	    		forceFit: true,
	    		getRowClass: function(record, index) {
		    		if (!record.data.compare_result) {
		    			return 'red';
		    		}
		    	}
	   		};
	        this.columns = [
	        	{ dataIndex: 'name',  header: 'Name', sortable: false, width: 200 },
	        	{ dataIndex: 'compare_result',  header: 'Status', sortable: false, width: 200, renderer: function(value) {
	        		return '<a href="" class="ico '+(value?'accept':'cross')+'"></a>';
	        	} }
	        ];
	        
	        this.buttonAlign = 'right';
			this.buttons = [{
				text: 'Aktualisieren',
				handler  : function(){
					Window.Path.store.load();
				}
			},{
				text: 'Weiter',
				handler  : function(){
					Window.showItem('File');
				}
			}];
				        
	        Path.superclass.initComponent.call(this);
	    }
	});
	Shopware.Check.Path = Path;
})();
</script>