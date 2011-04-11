<script type="text/javascript">
Shopware.Update.Detail = Ext.extend(Ext.FormPanel,{
	closable:true,
    border: false,
    autoScroll:true,
	layout:'border',
	baseParams: { updateID:0 },
	labelWidth: 180,
	buttonAlign:'right',
	initComponent: function() {
		
		this.AddValueField = new Ext.form.TextField({
			selectOnFocus: true,
			width: 120
		});

		this.Values = new Ext.grid.EditorGridPanel({
			frame:true,
			title:'Antworten',
			region:'center',
			stripeRows: true,
			clicksToEdit:1,
			store: new Ext.data.Store({
				url: '{url action=getUpdateValues}',
				baseParams: this.baseParams,
				reader: new Ext.data.JsonReader({
					root: 'data',
					totalProperty: 'count',
					id: 'id',
					fields: [
						'id', 'value', 'position', 'votes', 'real_votes', 'active'
					]
				})
			}),
			selModel: new Ext.grid.RowSelectionModel({ singleSelect: true }),
			cm: new Ext.grid.ColumnModel([
				{ id:'value', header: "Wert", width: 40, sortable: true, dataIndex: 'value', editor: new Ext.form.TextField() },
				{ id:'position', header: "Position", width: 40, sortable: true, dataIndex: 'position', editor: new Ext.form.NumberField() },
				{ id:'votes', header: "Anz. Stimmen", width: 40, sortable: true, dataIndex: 'votes', editor: new Ext.form.NumberField() },
				{ id:'real_votes', header: "Echte Stimmen", width: 40, sortable: true, dataIndex: 'real_votes' },
				{ id:'active', header: "Aktiv", width: 30, sortable: true, dataIndex: 'active', editor: new Ext.form.Checkbox(), renderer: function(v) { return v ? 'ja' : 'nein' } }
			]),
			viewConfig: {
				forceFit:true
			},
			tbar:[{
				text:'Wert löschen',
				handler: function (a, b, c){
					Ext.MessageBox.confirm('Confirm', 'Wollen Sie wirklich diesen Wert löschen?', function(r){
						if(r=='yes') {
							var id = this.Values.selModel.getSelected().id;
							this.Values.store.load({ params:{ 'delete': id } });
						}
					}, this);
				},
				iconCls:'delete',
				scope: this
			}, '-', this.AddValueField,{
				text:'Wert hinzufügen',
				handler: function (a, b, c){
					var value = this.AddValueField.getValue();
					var grid = this.Values;
	   				var count = grid.store.getCount();
	   				
	   				var r = Ext.data.Record.create([{ name: 'value' }]);
					var c = new r({	value: value});
					grid.stopEditing();
					grid.store.insert(count, c);
					grid.startEditing(count, 0);
				},
				iconCls:'add',
				scope: this
			}]
		});
		
		this.Tree = new Ext.tree.TreePanel({
			columnWidth:.5,
			fitToFrame: true,
			animate: false,
			title: 'Shops',
			height: '200',
			margins: '0 0 0 0',
			//minSize: 175,
			//enableDD: true,
			autoScroll: true,
			rootVisible: false,
			border: true,
			root: new Ext.tree.AsyncTreeNode({ id: '0' }),
			loader: new Ext.tree.TreeLoader({
				baseParams: this.baseParams,
				dataUrl:'{url action=getUpdateShops}'
			})
		});
			
		
		this.items = [{
			layout:'column',
			border:false,
			region: 'north',
			height: 200,
			items:[{
				columnWidth:.5,
				layout: 'form',
				bodyStyle:'padding:10px',
				border:false,
				defaults: { anchor: '100%',xtype:'textfield' },
				items: [{
					fieldLabel: 'Name',
					name: 'name'
				},{
					fieldLabel: 'Beschreibung',
					name: 'description'
				},{
					fieldLabel: 'Aktiv',
					name: 'active',
					xtype: 'checkbox',
					value: 1
				},{
					fieldLabel: 'Ausgabe',
					name: 'block',
					hiddenName:'block',
					valueField:'id',
					displayField:'name',
					triggerAction:'all',
					xtype: 'combo',
					allowBlank:false,
					mode: 'remote',
					emptyText:'Bitte wählen...',
	                selectOnFocus:true,
	                forceSelection : true,
					store:  new Ext.data.Store({
						url: '{url action="getUpdateBlocks"}',
						autoLoad: true,
						reader: new Ext.data.JsonReader({
							root: 'data',
							totalProperty: 'count',
							id: 'id',
							fields: ['id', 'name']
						})
					})
				},{
					fieldLabel: 'Eigenen Vorschlag erlauben',
					name: 'allow_suggestion',
					xtype: 'checkbox',
					value: 1
				}]
			},this.Tree]
		}, this.Values];
		
		this.buttons = [{
			text: 'Reset',
			handler: function(){
				this.load({ url:'{url action=getUpdates}', waitMsg:'Laden...' });
			},
			scope: this
		},{
			text: 'Speichern',
			handler: function(){
				
				var params = {};
				this.Values.store.each(function(record, i){
            		for (key in record.data) {
            			var value = record.data[key];
            			if(typeof value == 'boolean') {
            				value = value ? 1 : 0;
            			}
            			params['values['+i+']['+key+']'] = value;
            		}
            	});
            	
                Ext.each(this.Tree.getChecked(), function(node, i){
                    params['shops['+i+']'] = node.id;
                });
                
                params.updateID = this.baseParams.updateID;
            					
				this.getForm().submit({ url:'{url action=saveUpdate}', params: params, waitMsg:'Speichern...', success: function (el, r){
					if(r&&r.result) {
						var updateID = r.result.updateID;
						this.baseParams.updateID = updateID;
						this.Values.store.baseParams.updateID = updateID;
						this.Tree.loader.baseParams.updateID = updateID;
						var name = this.getForm().findField('name').getValue();
						this.setTitle('Umfrage: '+name);
					}
					this.Tree.root.reload();
					this.Values.store.load();
					Update.Grid.store.load();
				}, scope: this });
			},
			scope: this
		}];
	    Shopware.Update.Detail.superclass.initComponent.call(this);
	    Update.Tabs.add(this).show();
	    this.Tree.root.reload();
	    this.Values.store.load();
	    this.load({ url:'{url action=getUpdates}', waitMsg:'Laden...' });
	}
});
</script>