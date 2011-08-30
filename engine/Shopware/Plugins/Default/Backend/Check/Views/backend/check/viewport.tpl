<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var Tab = Ext.extend(Ext.TabPanel, {
		activeTab: 0,
		defaults: { autoScroll: true },
		deferredRender: false
	});
	Shopware.Check.Tab = Tab;
})();
</script>
<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var Viewport = Ext.extend(Ext.Viewport, {
	    layout: 'fit',
	    initComponent: function() {
	    	
	    	this.List = new Shopware.Check.List;
			this.Path = new Shopware.Check.Path;
			this.File = new Shopware.Check.File;
			this.Info = new Shopware.Check.Info;
			
			this.Tab = new Shopware.Check.Tab({
				items: [this.List, this.Path, this.File, this.Info]
			});
			
			this.showItem = function(item) {
				this[item].enable();
				this.Tab.activate(this[item]);
			};

			this.items = [this.Tab];
			
			Viewport.superclass.initComponent.call(this);
	    }
	});
	Shopware.Check.Viewport = Viewport;
})();
</script>