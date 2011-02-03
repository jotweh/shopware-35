{extends file="../_default/frontend/index/header.tpl"}

{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='templates/clean/frontend/_resources/styles/clean.css'}" />
{/block}

{block name="frontend_index_header_css_ie" append}
	<!--[if lte IE 6]>
		<link type="text/css" rel="stylesheet" media="all" href="{link file='frontend/_resources/styles/ie6_clean.css'}" />
	<![endif]-->
{/block}