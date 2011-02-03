{extends file="backend/index/index.tpl"}

{block name='backend_index_header_title'}
	Shopware Backend (c) 2010,2011 shopware AG
{/block}

{block name="backend_index_body_attributes"}{/block}
{block name="backend_index_index_menu"}{/block}
{block name="backend_index_index_search"}{/block}
{block name="backend_index_index_instantmessenger"}{/block}
{block name="backend_index_index_footer"}
<div id="footer"></div>
{/block}
{block name="backend_index_index_footer_tabs"}{/block}
{block name="backend_index_index_account"}{/block}
{block name="backend_index_index_ticket"}{/block}
{block name="backend_index_index_onload"}
{/block}
{block name="backend_index_index_extjs"}
	<script>
	if (self != top) { 
    	parent.location.href=self.location.href; 
	}	 

	Ext.ns('Shopware.Auth.Components');
	
	{include file="backend/auth/loginpanel.tpl"}
	
	Ext.ns('Shopware.Auth');
	(function(){
		var Auth = Ext.extend(Ext.Viewport,{
			forceFit: true,
			hideMode: "offsets",
			layout: 'absolute',
			initComponent:function() {
				this.items = [this.LoginPanel];
				Auth.superclass.initComponent.call(this);
			},
			constructor: function(config){
				Ext.apply(this,config);
				this.loadWidgets();
				Auth.superclass.constructor.call(this);
			},
			loadWidgets: function(){
				this.LoginPanel = new Shopware.Auth.Components.LoginPanel({ parent:this });
			},
			initEvents: function(){
			}
		});
		Shopware.Auth.Viewport = Auth;
	})();
	
	
	Ext.onReady(function(){
		{if $BrowserError}
			alert('Im Augenblick werden nur folgende Browser im Backend unterstützt: Firefox, Safari, Chrome. Eine IE-Unterstützung befindet sich in Arbeit!');
		{else}
			Auth = new Shopware.Auth.Viewport;
		{/if}
	});
	
	</script>
{/block}