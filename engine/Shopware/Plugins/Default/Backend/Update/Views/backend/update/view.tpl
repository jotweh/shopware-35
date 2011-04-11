<script type="text/javascript">
View = Ext.extend(Ext.Viewport, {
	layout: 'fit',
	initComponent: function() {

		this.BackupForm = new Shopware.Update.Backup();
		this.ConfigForm = new Shopware.Update.Config();
		this.BackupList = new Shopware.Update.BackupList();
		this.HandlerFrom = new Shopware.Update.Handler();

		this.Tabs = new Ext.TabPanel({
	        activeTab: 0,
	        items: [this.BackupList, this.BackupForm, this.ConfigForm, this.HandlerFrom]
	    });
		
	    this.items = [this.Tabs];
	    
        View.superclass.initComponent.call(this);
	}
});
Shopware.Update.View = View;
</script>