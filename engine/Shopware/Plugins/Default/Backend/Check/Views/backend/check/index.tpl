{extends file="backend/index/parent.tpl"}

{block name="backend_index_css" append}
<style type="text/css">
/*<![CDATA[*/
	a.ico {
		height:16px;
		margin:0 0 0 5px;
		padding:0;
		width:20px;
		cursor:pointer;
		float:left;
	}
	.form_text .x-panel-body {
		font-family:Arial,Verdana,Helvetica,sans-serif;
		font-size:12px;
		font-size-adjust:none;
		line-height:148%;
		padding: 5px
	}
	.info {
	    color: red;
	    font-size: 10px;
	}
	.num { 
		float: left; 
		color: gray; 
		text-align: right; 
		margin-right: 6pt; 
		padding-right: 6pt; 
		border-right: 1px solid gray;
	}
	pre {
		white-space: pre;
	}
/*]]>*/
</style>
{/block}

{block name="backend_index_javascript" append}
{include file="backend/check/viewport.tpl"}
{include file="backend/check/list.tpl"}
{include file="backend/check/path.tpl"}
{include file="backend/check/file.tpl"}
<script type="text/javascript">
//<![CDATA[
	var Check;
	Ext.onReady(function(){
		var Check = new Shopware.Check.Viewport;
	});
//]]>
</script>
{/block}