{extends file='frontend/index/index.tpl'}

{block name='frontend_index_header'}
	{include file='frontend/blog/header.tpl'}
{/block}

{* Main content *}
{block name='frontend_index_content'}
<div class="grid_13" id="blog">
	{* Banner *}
	{block name='frontend_blog_index_banner'}
		{include file="frontend/listing/banner.tpl"}
	{/block}
	{if $sSupplierInfo} 
	<div id="supplierfilter">
		{if $sSupplierInfo.image}
			<img src="{$sSupplierInfo.image}" alt="{$sSupplierInfo.name}" name="{$sSupplierInfo.name}" border="0" title="{$sSupplierInfo.name}" />
		{else}
			{se name='ListingInfoFilterSupplier'}{/se} <strong>{$sSupplierInfo.name}</strong>
		{/if}
		<div class="right">
			<a href="{$sSupplierInfo.link}" title="{s name='ListingLinkAllSuppliers'}{/s}" class="bt_allsupplier">
				{se name='ListingLinkAllSuppliers'}{/se}
			</a>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
	<div class="space">&nbsp;</div>
	{/if}
	{* Blog listing *}
	{block name='frontend_blog_index_listing'}
		{include file="frontend/blog/listing.tpl"}
	{/block}
</div>
{/block}

{* Sidebar right *}
{block name='frontend_index_content_right'}
<div id="right" class="grid_3 last">
	
	{* Campaign top *}
	{block name='frontend_blog_index_campaign_top'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightTop}
	{/block}
	
	<div class="blog_navi">
	
		{* Subscribe Atom + RSS *}
		{block name='frontend_blog_index_subscribe'}
		<h2 class="headingbox">Subscribe</h2>
		<div class="blogInteract">
			<ul>
				<li><a class="rss" href="{$sCategoryContent.rssFeed}" title="{$sCategoryContent.description}">{se name="BlogLinkRSS"}{/se}</a></li>
				<li class="last"><a class="atom" href="{$sCategoryContent.atomFeed}" title="{$sCategoryContent.description}">{se name="BlogLinkAtom"}{/se}</a></li>
			</ul>
		</div>
		{/block}
	
		{* Blog filter *}
		{block name='frontend_blog_index_filter'}
			{include file="frontend/blog/filter.tpl"}
		{/block}
	</div>
	{* Campaign bottom *}
	{block name='frontend_blog_index_campaign_bottom'}
		{include file="frontend/campaign/box.tpl" sCategoryCampaigns=$sCampaigns.rightBottom}
	{/block}
</div>
{/block}