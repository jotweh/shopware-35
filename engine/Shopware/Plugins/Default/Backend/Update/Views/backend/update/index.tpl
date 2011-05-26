{extends file="backend/index/parent.tpl"}

{block name="backend_index_css" append}
<link href="{link file='engine/backend/css/icons4.css'}"  rel="stylesheet" type="text/css" />
<style type="text/css">
	a.ico {
		height:16px;
		margin:0 0 0 5px;
		padding:0;
		width:16px;
		cursor:pointer;
		float:left;
	}
</style>
{/block}

{block name="backend_index_javascript" append}
<script type="text/javascript">
Ext.ns('Shopware.Update');	
</script>
{include file='backend/update/view.tpl'}
{include file='backend/update/info.tpl'}
{include file='backend/update/backup.tpl'}
{include file='backend/update/backup_list.tpl'}
{include file='backend/update/config.tpl'}
{include file='backend/update/handler.tpl'}
{include file='backend/update/detail.tpl'}
<script type="text/javascript">
var Update;
Ext.onReady(function(){
	Ext.QuickTips.init();
	Update = new Shopware.Update.View;
});
</script>
{/block}