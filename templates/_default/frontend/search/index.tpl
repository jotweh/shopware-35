{extends file='frontend/index/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
	<div id="center" class="grid_13">
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
			{include file='frontend/listing/listing_actions.tpl'}
		{/if}
	{/block}
	</div>
{/block}

{block name='frontend_listing_actions_top'}

{if $sPages.numbers|@count > 1}
<div class="top">
	<div class="sort-filter">&nbsp;</div>
	<form method="post" action="{$sPages.first}">
	<div class="articleperpage rightalign">
		<label>{s name='ListingLabelItemsPerPage'}Artikel pro Seite:{/s}</label>
		<select name="sPerPage" class="auto_submit">
		{foreach from=$sPerPage item=perPage}
	        <option value="{$perPage.value}" {if $perPage.markup}selected="selected"{/if}>{$perPage.value}</option>
		{/foreach}
		</select>
	</div>
	</form>
</div>
{/if}
{/block}

{block name='frontend_listing_actions_paging'}
{if $sPerPage}
<div class="bottom">
	<div class="paging">
		<label>{se name='ListingPaging'}Blättern:{/se}</label>
		
		{if $sPages.previous}
			<a href="{$sPages.previous}" title="{s name='ListingLinkNext'}{/s}" class="navi prev">
				{s name="ListingTextPrevious"}&lt;{/s}
			</a>
		{/if}
		
		{foreach from=$sPages.numbers item=page}
			{if $page.markup}
				<a title="" class="navi on">{$page.value}</a>
			{else}
				<a href="{$page.link}" title="" class="navi">
					{$page.value}
				</a>
			{/if}
		{/foreach}
		
		{if $sPages.next}
			<a href="{$sPages.next}" title="{s name='ListingLinkPrevious'}{/s}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
		{/if}
	</div>
	<div class="display_sites">
		{se name="ListingTextSite"}Seite{/se} <strong>{$sPage}</strong> {se name="ListingTextFrom"}von{/se} <strong>{$sNumberPages}</strong>
	</div>
</div>
{/if}
</div>
{/block}

{block name="frontend_listing_actions_class"}
<div class="listing_actions{if !$sPages || $sPages.numbers|@count < 2} normal{/if}">
{/block}