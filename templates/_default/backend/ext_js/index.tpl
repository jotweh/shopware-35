{extends file="backend/index/parent.tpl"}

{block name="backend_index_css"}
<link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon">
<link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon"> 
<link rel="stylesheet" type="text/css" href="{link file='engine/backend/css/icons.css'}" />
<link rel="stylesheet" type="text/css" href="{link file='backend/_resources/javascript/ext-4.0.0/resources/css/ext-all.css'}" />
{/block}

{block name="backend_index_javascript"}
<script type="text/javascript" src="{link file='backend/_resources/javascript/ext-4.0.0/bootstrap.js'}" charset="utf-8"></script>
{if $Locale}
{$link = {link file="backend/_resources/javascript/ext-4.0.0/locale/ext-lang-{$Locale->getLanguage()}_{$Locale->getRegion()}.js"}}
{if !$link}
{$link = {link file="backend/_resources/javascript/ext-4.0.0/locale/ext-lang-{$Locale->getLanguage()}.js"}}
{/if}
{if $link}<script type="text/javascript" src="{$link}" charset="utf-8"></script>
{/if}
{/if}
<script type="text/javascript">
//<![CDATA[
Ext.Loader.setConfig({
	enabled: true,
	paths: {
		'Shopware': '{url controller=ext_js action=load}'
	},
	suffixes: {
		'Shopware': ''
	}
});

Ext.Loader.getPath = function(className) {
	var tempClass = className;
	var path = '',
	paths = this.config.paths,
	prefix = this.getPrefix(className);
	suffix = this.config.suffixes[prefix] !== undefined ? this.config.suffixes[prefix] : '.js';
	
	if (prefix.length > 0) {
		if (prefix === className) {
			return paths[prefix];
		}

		path = paths[prefix];
		className = className.substring(prefix.length + 1);
	}

	if (path.length > 0) {
		path += '/';
	}

	return path.replace(/\/\.\//g, '/') + className.replace(/\./g, "/") + suffix;
};
{block name="backend_index_javascript_inline"}{/block}
//]]>
</script>
{/block}