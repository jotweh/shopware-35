<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var List = Ext.extend(Ext.grid.GridPanel, {
	    title: 'Server-Konfiguration',
	    initComponent: function() {
	    	this.bbar = {
				xtype: 'panel',
				cls : 'form_text',
				border: false,
				html: '<span class="info">* Angaben stimmen unter Umständen nicht</span>'
			};
	    	this.store = new Ext.data.GroupingStore({
	   			url: '{url action=checkSystemList}',
	   			groupField:'group',
	   			autoLoad: true,
	   			sortInfo: { field: 'position', direction: 'ASC' },
	   			reader: new Ext.data.JsonReader({
	   				root: 'data',
	   				totalProperty: 'count',
	   				id: 'position',
	   				fields: [
	   					'name', 'required_version', 'version', 'compare_result', 'group', 'position', 'info'
	   				]
	   			})
	    	});
	    	this.view = new Ext.grid.GroupingView({
	            forceFit:true,
	            //groupTextTpl: '{ text }',
	            showGroupName: false,
	    	});
	    	this.getView().getRowClass = function(record, index) {
	    		if (!record.data.compare_result) {
	    			return 'red';
	    		}
	    	};
	        this.columns = [
	        	{ dataIndex: 'name',  header: 'Name', sortable: false, width: 200, renderer: function(value, e, r) {
	        		if(r.data.info) {
	        			value += '<span class="info">*</span>';
	        		}
	        		return value;
	        	} },
	        	{ dataIndex: 'required_version',  header: 'Benötigt', sortable: false, width: 200, align: 'right', renderer: function(value) {
	        		if(value==false) {
	        			return '0';
	        		} else if(value==true) {
	        			return '1';
	        		} else {
	        			return value;
	        		}
	        	} },
	        	{ dataIndex: 'version',  header: 'Vorhanden', sortable: false, width: 200, align: 'right', renderer: function(value) {
	        		if(value==false) {
	        			return '0';
	        		} else if(value==true) {
	        			return '1';
	        		} else {
	        			return value;
	        		}
	        	} },
	        	{ dataIndex: 'compare_result',  header: 'Status', sortable: false, width: 200, renderer: function(value) {
	        		return '<a href="" class="ico '+(value?'accept':'cross')+'"></a>';
	        	}},
	            { dataIndex: 'group', header: "Gruppe", width: 40, sortable: false, hidden: true, groupRenderer:function(value) {
	            	switch(value) {
	            		case 'core':
	            			return 'Allgemein';
	            		case 'extension':
	            			return 'Erweiterungen';
	            		case 'config':
	            			return 'Einstellungen';
	            		case 'other':
	            			return 'Sonstiges (Optional)';
	            		default:
	            			return value;
	            	}
	            } }
	        ];
	        
	        this.buttonAlign = 'right';
			this.buttons = [{
				text: 'Aktualisieren',
				handler  : function(){
					Window.refreshList();
				}
			},{
				text: 'Weiter',
				handler  : function(){
					Window.showItem('Config');
				}
			}];
				        
	        List.superclass.initComponent.call(this);
	    }
	});
	Shopware.Check.List = List;
})();
</script>