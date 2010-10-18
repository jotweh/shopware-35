{extends file='backend/index/parent.tpl'}

{block name="backend_index_css" append}
	<link href="{link file='backend/_resources/styles/Ext.ux.FileUploadField.css'}" rel="stylesheet" type="text/css" />
	<style type="text/css">
	.inactive {
		opacity: 0.5;
	}
	a.ico {
		height:20px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
		cursor:pointer;
		float:right;
	}
	</style>
{/block}

{block name="backend_index_javascript" append}
	<script type="text/javascript" src="{link file='backend/_resources/javascript/plugins/Ext.ux.FileUploadField.js'}"></script>
	<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery-1.4.2.js'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/css/icons.css'}"></script>
	<script type="text/javascript" src="{link file='engine/backend/css/icons4.css'}"></script>
	
	{include file='backend/plugin/list.tpl'}
	{include file='backend/plugin/upload.tpl'}
	{include file='backend/plugin/viewport.tpl'}
<script type="text/javascript">
//<![CDATA[
var Viewport
Ext.onReady(function(){
	Viewport = new Shopware.Plugin.Viewport;
});
//]]>
</script>
{/block}