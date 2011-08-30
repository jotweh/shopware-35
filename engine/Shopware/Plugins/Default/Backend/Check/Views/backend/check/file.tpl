<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var File = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Shopware-Dateien',
	    initComponent: function() {
	    	this.bbar = {
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				html: '<span class="info">Achtung, wenn die Überprüfung fehlschlägt, liegt es wahrscheinlich daran, dass die Dateien nicht richtig hochgeladen wurden. Die Dateien müssen dann nochmal im Binary-Mode hochgeladen werden. </span>'
			};
	    	this.store = new Ext.data.Store({
	   			url: '{url action=checkFileList}',
	   			autoLoad: true,
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'position',
	   				fields: [
	   					'name', 'result', 'position', 'version'
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
	        	{ dataIndex: 'version',  header: 'Vorhanden', sortable: false, width: 200, align: 'right', renderer: function(value) {
	        		if(value==false) {
	        			return '0';
	        		} else if(value==true) {
	        			return '1';
	        		} else {
	        			return value;
	        		}
	        	} },
	        	{ dataIndex: 'result',  header: 'Status', sortable: false, width: 200, renderer: function(value) {
	        		return '<a href="" class="ico '+(value?'accept':'cross')+'"></a>';
	        	} }
	        ];
	        
	        this.buttonAlign = 'right';
			this.buttons = [{
				text: 'Aktualisieren',
				handler  : function(){
					this.store.load();
				},
				scope: this
			}];
				        
	        File.superclass.initComponent.call(this);
	    }
	});
	Shopware.Check.File = File;
})();
</script>