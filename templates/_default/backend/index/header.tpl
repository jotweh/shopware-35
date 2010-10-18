<head>
{* Http-Tags *}
{block name="backend_index_meta_http_tags"}
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
{/block}

{* Meta-Tags *}
{block name='backend_index_meta_tags'}
<meta name="robots" content="noindex,nofollow" />
{/block}

{* Page title *}
<title>{block name='backend_index_header_title'}{s name='IndexTitle'}{/s}{/block}</title>

{* Stylesheets and Javascripts *}
{block name="backend_index_css_screen"}
{/block}

{block name="backend_index_css"}
<link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon">
<link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon"> 
<link rel="stylesheet" type="text/css" href="{link file='engine/backend/css/icons.css'}" />
<link rel="stylesheet" type="text/css" href="{link file='engine/vendor/ext/resources/css/ext-all.css'}" />
<style type="text/css">
button.pencil {
	background: url({link file='engine/backend/img/default/icons/pencil.png'}) no-repeat 0px 0px;
}
button.delete {
	background: url({link file='engine/backend/img/default/icons/delete.png'}) no-repeat 0px 0px;
}
button.add {
	background: url({link file='engine/backend/img/default/icons/add.png'}) no-repeat 0px 0px;
}
button.folders_plus {
	background: url({link file='engine/backend/img/default/icons4/folders_plus.png'}) no-repeat 0px 0px;
}
button.refresh {
	background: url({link file='engine/backend/img/default/icons4/arrow_circle_double_135.png'}) no-repeat 0px 0px;
}
</style>
{/block}

{block name="backend_index_javascript"}
<script type="text/javascript" src="{link file='engine/vendor/ext/adapter/ext/ext-base.js'}"></script>
<script type="text/javascript" src="{link file='engine/vendor/ext/ext-all-debug.js'}"></script>
<script type="text/javascript" src="{link file='engine/vendor/ext/build/locale/ext-lang-de.js'}" charset="utf-8"></script>
<script type="text/javascript">
//<![CDATA[
{block name="backend_index_javascript_inline"}{/block}
//]]>
</script>
{/block}
</head>