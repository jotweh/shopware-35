<script type="text/javascript">
BackupList = Ext.extend(Ext.grid.GridPanel, {
	//title: 'Backup-Verwaltung',
	closable: false,
	viewConfig: {
		forceFit:true
	},
	initComponent: function() {
		this.refreshList = function() {
			this.store.load();
		};
		this.deleteBackup = function(file) {
			Ext.MessageBox.show({
				title: 'Backup löschen',
				msg: 'Wollen Sie wirklich diesen Backup löschen?',
				buttons: Ext.MessageBox.YESNOCANCEL,
				fn: function(btn, text){
					if(btn=="yes") {
						this.store.load({ params: { 'delete': file } });
					}
				},
				animEl: 'mb4',
				scope: this,
				icon: Ext.MessageBox.QUESTION
			});
		};
		this.store = new Ext.data.Store({
			url: '{url action=backupList}',
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count',
				id: 'file',
				fields: [
					'file', 'name', 'size', { name: 'added', type: 'date', dateFormat: 'timestamp' }
				]
			})
		});
		this.sm = new Ext.grid.RowSelectionModel({ singleSelect: true });
		this.cm = new Ext.grid.ColumnModel([
			{ header: "Name", width: 40, sortable: true, dataIndex: 'name' },
			//{ id:'description', header: "Beschreibung", width: 40, sortable: true, dataIndex: 'description' },
			{ header: "Größe", width: 40, sortable: true, dataIndex: 'size', renderer: function(value) { return Ext.util.Format.fileSize(value); } },
			{ header: "Erstellt am", width: 30, sortable: true, renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), dataIndex: 'added' },
			{ header: "&nbsp;", width: 30, sortable: true, renderer: function(value) {
				return '<a title="Backup herunterladen" class="ico attach" href="{url action=downloadBackup}?file='+escape(value)+'"></a>'+
					'<a title="Backup löschen" class="ico delete" href="#" onclick="Update.BackupList.deleteBackup(\''+escape(value)+'\'); return false;"></a>';
			}, dataIndex: 'file' }
		]);
		
        BackupList.superclass.initComponent.call(this);
        
        this.store.load();
	}
});
Shopware.Update.BackupList = BackupList;
</script>