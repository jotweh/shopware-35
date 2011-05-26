<script type="text/javascript">
View = Ext.extend(Ext.Viewport, {
	layout: 'fit',
	initComponent: function() {

		this.Info = new Shopware.Update.Info();
		this.BackupForm = new Shopware.Update.Backup();
		this.ConfigForm = new Shopware.Update.Config();
		this.HandlerFrom = new Shopware.Update.Handler();

		this.Tabs = new Ext.TabPanel({
	        activeTab: 0,
	        items: [this.Info, this.ConfigForm, this.HandlerFrom, this.BackupForm]
	    });
		
	    this.items = [this.Tabs];
	    
        View.superclass.initComponent.call(this);
	}
});
Shopware.Update.View = View;
</script>