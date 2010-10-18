{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
	<div id="center" class="grid_16">
	{block name='frontend_search_index_headline'}
		<h2>{s name='SearchHeadline'}Zu "{$sSearchTerm|escape}" wurden {$sSearchResultsNum|escape} Artikel gefunden{/s}</h2>
	{/block}
	{block name='frontend_search_index_result'}
		<div class="listing" id="listing">
			{foreach from=$sSearchResults item=sArticle key=key name=list}
				{include file="frontend/listing/box_article.tpl"}
			{/foreach}
		</div>
		{if $sSearchResults|@count && $sPages|@count}
			<div class="clear">&nbsp;</div>
			{include file='frontend/search/paging.tpl' sPages=$sPages}
		{/if}
	{/block}
	</div>
{/block}