<div class="listing-blog">
	{if $sCategoryContent.cmsheadline || $sCategoryContent.cmstext}
		{include file="frontend/listing/text.tpl"}
	{/if}
	
	{if $sOffers}
		{foreach from=$sOffers item=offer key=key name="counter"}
				{if $offer.mode == "gfx"}
					{include file="frontend/listing/promotion_image.tpl" sArticle=$offer}
				{else}
					{include file="frontend/blog/box.tpl" sArticle=$offer}
				{/if}	
		{/foreach}
	{/if}
	{if $sArticles}
		{* Sorting and changing layout *}
		{block name="frontend_listing_top_actions"}
			{include file='frontend/blog/listing_actions.tpl'}
		{/block}
	
		{foreach from=$sArticles item=article key=key name="counter"}
			{include file="frontend/blog/box.tpl" sArticle=$article key=$key}
		{/foreach}
		
		{* Paging *}
		{block name="frontend_listing_bottom_paging"}
			{include file='frontend/blog/listing_actions.tpl'}
		{/block}
	{/if}
</div>

