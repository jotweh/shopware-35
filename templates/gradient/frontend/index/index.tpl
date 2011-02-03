{extends file="../_default/frontend/index/index.tpl"}

{* Search *}
	{block name='frontend_index_search'}
	
	{/block}
	
	{block name='frontend_index_navigation' prepend}
	
	{include file="frontend/index/search.tpl"}
	
	{/block}