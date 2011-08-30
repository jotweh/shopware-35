<script type="text/javascript">
Ext.ns('Shopware.Check');
(function(){
	var Info = Ext.extend(Ext.Panel, {
	    title: 'PHP-Info',
	    autoScroll: false,
	    html: '<iframe frameborder="0" width="100%" height="100%" src="{url action=info}"></iframe>',
	    initComponent: function() {    
	        Info.superclass.initComponent.call(this);
	    }
	});
	Shopware.Check.Info = Info;
})();
</script>